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

    protected static $instances = array();

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

    public static function instance($name)
    {
        return static::factory($name);
    }

    /**
     * Enable to add salt to increase the security of the system
     *
     * @access  public
     * @param   string  $password
     * @return  string
     */
    public function add_salt($password = '') 
    {
        $salt =  \Config::get('app.salt', \Config::get('crypt.crypto_key'));

        return \sha1($salt . $password);
    }

    public static function login($user, $password = null, $name = 'normal')
    {
        return static::factory($name)->login($user, $password);
    }

    public static function logout()
    {
        foreach (static::$instances as $instance)
        {
            $instance->logout(false);
        }
    }

}