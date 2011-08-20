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
 * as possible so that we can support the most basic structure available
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
 
class Auth {

    /**
     * Cache auth instance so we can reuse it on multiple request eventhough 
     * it's almost impossible to happen
     * 
     * @static
     * @access  protected
     * @var     array
     */
    protected static $instances = array();

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
        if (\is_null($name))
        {
            $name = 'user';
        }

        $name = \Str::lower($name);

        if (!isset(static::$instances[$name]))
        {
            $driver = '\\Hybrid\\Auth_' . ucfirst($name);

            // instance has yet to be initiated
            if (\class_exists($driver))
            {
                static::$instances[$name] = new $driver();
            }
            else
            {
                throw new \Fuel_Exception("Requested {$driver} does not exist");
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
    public static function add_salt($password = '') 
    {
        $salt =  \Config::get('app.salt', \Config::get('crypt.crypto_key'));

        return \sha1($salt . $password);
    }

    /**
     * Login based on available Auth_Driver.
     *
     * @static
     * @access  public
     * @param   string  $username       A string of either `user_name` or `email` field from table `users`.
     * @param   string  $password       An unhashed `password` or `token` string from external API.
     * @param   string  $type           Connection type string, default to 'normal'.
     * @return  bool
     * @throws  \Fuel_Exception
     */
    public static function login($username, $password, $name = 'normal')
    {
        return static::factory($name)->login($username, $password);
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

}