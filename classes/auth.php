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
     * Initiate a new Auth instance
     * 
     * @static
     * @access  public
     * @return  Auth_Abstract
     * @throws  \Fuel_Exception
     */
    public static function factory($name)
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
     * Return instance (or create a new one if not available yet)
     *
     * @static
     * @access  public
     * @return  Auth_Abstract
     * @see     self::factory
     */
    public static function instance($name)
    {
        return static::factory($name);
    }

    /**
     * Enable to add salt to increase the security of the system
     *
     * @static
     * @access  public
     * @param   string  $password
     * @return  string
     */
    public static function add_salt($password = '') 
    {
        $salt =  \Config::get('app.salt', \Config::get('crypt.crypto_key'));

        return \sha1($salt . $password);
    }

    /**
     * Login based on available Auth_Abstract
     *
     * @static
     * @access  public
     * @return  bool
     * @throws  \Fuel_Exception
     */
    public static function login($user, $password = null, $name = 'normal')
    {
        return static::factory($name)->login($user, $password);
    }

    /**
     * Logout from all loaded instances
     *
     * @static
     * @access  public
     * @return  bool
     */
    public static function logout()
    {
        foreach (static::$instances as $instance)
        {
            $instance->logout(false);
        }

        return true;
    }

}