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

class Auth 
{    
    /**
     * Cache Auth instance so we can reuse it on multiple request.
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
     * @throws  \FuelException
     */
    public static function redirect($type)
    {
        $path = \Config::get("autho.urls.{$type}");

        if (null === $path)
        {
            throw new \FuelException(__METHOD__.": Unable to redirect using {$type} type.");
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
     * @throws  \FuelException
     */
    public static function forge($name = null)
    {
        if (null === $name)
        {
            $name = 'user';
        }

        $name = strtolower($name);

        if ( ! isset(static::$instances[$name]))
        {
            $driver = '\\Hybrid\\Auth_Driver_'.ucfirst($name);

            if ( !! class_exists($driver))
            {
                static::$instances[$name] = new $driver();
            }
            else
            {
                throw new \FuelException("Requested {$driver} does not exist.");
            }
        }

        return static::$instances[$name];
    }

    /**
     * Shortcode to self::forge().
     *
     * @deprecated  1.3.0
     * @static
     * @access  public
     * @param   string  $name
     * @return  self::forge()
     */
    public static function factory($name = null)
    {
        \Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
        
        return static::forge($name);
    }

    /**
     * Get cached instance, or generate new if currently not available.
     *
     * @static
     * @access  public
     * @return  Auth_Driver
     * @see     self::forge()
     */
    public static function instance($name = null)
    {
        return static::forge($name);
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

        return \sha1($salt.$string);
    }

    /**
     * Check if user has any of provided roles.
     * 
     * @static
     * @access  public
     * @param   mixed   $check_roles
     * @return  bool 
     */
    public static function has_roles($check_roles) 
    {
        $user = static::instance('user')->get();

        if ( ! is_array($check_roles)) 
        {
            $check_roles = func_get_args();
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

    /**
     * Login based on available Auth_Driver.
     *
     * @static
     * @access  public
     * @param   string  $username       A string of either `user_name` or `email` field from table `users`.
     * @param   string  $password       An unhashed `password` or `token` string from external API.
     * @param   string  $driver         Driver type string, default to 'user'.
     * @return  bool
     * @throws  \FuelException
     */
    public static function login($username, $password, $driver = 'user')
    {
        return static::forge($driver)->login($username, $password);
    }

    /**
     * Reauthenticate current user.
     *
     * @static
     * @access  public
     * @param   string  $driver         Driver type string, default to 'user'.
     * @return  bool
     * @throws  \FuelException
     */
    public static function reauthenticate($driver = 'user')
    {
        return static::forge($driver)->reauthenticate();
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

    /**
     * Link user account with external provider
     *
     * @static
     * @access  public
     * @param   int     $user_id
     * @param   array   $user_data
     * @return  bool
     */
    public static function link_account($user_id, $user_data)
    {
        if (empty($user_data) or ! isset($user_data['credentials']))
        {
            return ;
        }
        
        // some provider does not have secret key
        if ( ! isset($user_data['credentials']['secret']))
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
    
}