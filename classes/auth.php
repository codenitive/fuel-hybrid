<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Hybrid;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * Authentication Class
 * 
 * Why another class? FuelPHP does have it's own Auth package but what Hybrid does 
 * it not defining how you structure your database but instead try to be as generic 
 * as possible so that we can support the most basic structure available.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth {
    
    /**
     * Cache auth instance so we can reuse it on multiple request
     * 
     * @static
     * @access  protected
     * @var     array
     */
    protected static $instances = array();

    /**
     * Redirect user based on type
     *
     * @static
     * @access  protected
     * @param   string  $type
     * @return  void
     * @throws  \Fuel_Exception
     */
    public static function redirect($type)
    {
        $path = \Config::get("autho.urls.{$type}");

        if (is_null($path))
        {
            throw new \Fuel_Exception("\Hybrid\Auth_Driver: Unable to redirect using {$type} type.");
        }
        
        \Response::redirect($path);

        return true;
    }

    /**
     * Initiate a new Auth_Driver instance.
     * 
     * @static
     * @access  public
     * @param   string  $name       null to fetch the default driver, or a driver id to get a specific one
     * @return  Auth_Driver
     * @throws  \Fuel_Exception
     */
    public static function factory($name = null)
    {
        if (is_null($name))
        {
            $name = 'user';
        }

        $name = \Str::lower($name);

        if (!isset(static::$instances[$name]))
        {
            $driver = '\\Hybrid\\Auth_Driver_' . \Str::ucfirst($name);

            if (!!class_exists($driver))
            {
                static::$instances[$name] = new $driver();
            }
            else
            {
                throw new \Fuel_Exception("Requested {$driver} does not exist.");
            }
        }

        return static::$instances[$name];
    }

    /**
     * Retrieves a loaded driver, when drivers are set in config the first driver will also be the default. 
     *
     * @static
     * @access  public
     * @return  Auth_Driver
     * @see     self::factory()
     */
    public static function instance($name = null)
    {
        return static::factory($name);
    }

    /**
     * Turn string to hash using sha1() hash with salt.
     *
     * @static
     * @access  public
     * @param   string  $password       String to be hashed
     * @return  string
     */
    public static function add_salt($string = '')
    {
        $salt = \Config::get('autho.salt', \Config::get('crypt.crypto_key'));

        return \sha1($salt . $string);
    }

    /**
     * Login based on available Auth_Driver.
     *
     * @static
     * @access  public
     * @param   string  $username       A string of either `user_name` or `email` field from table `users`.
     * @param   string  $password       An unhashed `password` or `token` string from external API.
     * @param   string  $driver         Driver type string, default to 'user'.
     * @return  bool
     * @throws  \Fuel_Exception
     */
    public static function login($username, $password, $driver = 'user')
    {
        return static::factory($driver)->login($username, $password);
    }

    /**
     * Logout from all loaded instances.
     *
     * @static
     * @access  public
     * @return  bool
     */
    public static function logout()
    {
        foreach (static::$instances as $name => $instance)
        {
            $instance->logout(false);
        }

        return true;
    }

    public static function link_account($user_id, $user_data)
    {
        if (empty($user_data) or !isset($user_data['credentials']))
        {
            return ;
        }
        
        // some provider does not have secret key
        if (!isset($user_data['credentials']['secret']))
        {
            $user_data['credentials']['secret'] = null;
        }

        if ($user_id < 1)
        {
            return ;
        }

        \DB::select()
            ->from('authentications')
            ->where('user_id', '=', $user_id)
            ->where('provider', '=', $user_data['credentials']['provider'])
            ->execute();

        // Attach this account to the logged in user
        if (\DB::count_last_query() > 0)
        {
            \DB::update('authentications')->set(array(
                'uid'      => $user_data['credentials']['uid'],
                'token'    => $user_data['credentials']['token'],
                'secret'   => $user_data['credentials']['secret'],
            ))
            ->where('user_id', '=', $user_id)
            ->where('provider', '=', $user_data['credentials']['provider'])
            ->execute();
        }
        else
        {
            \DB::insert('authentications')->set(array(
                'user_id'  => $user_id,
                'provider' => $user_data['credentials']['provider'],
                'uid'      => $user_data['credentials']['uid'],
                'token'    => $user_data['credentials']['token'],
                'secret'   => $user_data['credentials']['secret'],
            ))->execute();
        }

        return true;
    }

    /**
     * Check if user has any of provided roles (however this should be in \Hybrid\User IMHO)
     * 
     * @static
     * @access  public
     * @param   mixed   $check_roles
     * @return  bool 
     */
    public static function has_roles($roles)
    {
        $user = static::instance('user')->get();

        if (!is_array($check_roles)) 
        {
            $check_roles = array($check_roles);
        }

        foreach ($user->roles as $role) 
        {
            $role = \Inflector::friendly_title($role, '-', TRUE);

            foreach ($check_roles as $check_against) 
            {
                if ($role == $check_against) 
                {
                    return true;
                }
            }
        }

        return false;
    }
    
}