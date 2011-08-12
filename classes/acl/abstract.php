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

abstract class Acl_Abstract {

    /**
     * @static
     * @access  protected
     * @var     object|array
     */
    protected static $items;

    public static function _init()
    {
        \Config::load('app', true);
        \Config::load('crypt', true);
    }

    /**
     * Return TRUE/FALSE whether visitor is logged in to the system
     * 
     * Usage:
     * 
     * <code>false === \Hybrid\Acl_User::is_logged()</code>
     *
     * @static
     * @access  public
     * @return  bool
     */
    public static function is_logged() 
    {
        return (static::$items['id'] > 0 ? true : false);
    }

    /**
     * Get current user authentication
     * 
     * Usage:
     * 
     * <code>$user = \Hybrid\Acl_User::get();</code>
     *
     * @static
     * @access  public
     * @return  object
     */
    public static function get($name = null) 
    {
        if (\is_null($name)) 
        {
            return (object) static::$items;
        }

        if (\array_key_exists($name, static::$items)) 
        {
            return static::$items[$name];
        }

        return false;
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
     * Redirect user based on type
     *
     * @static
     * @access  protected
     * @param   string  $type
     * @param   string  $default_route
     * @throws  \Fuel_Exception
     */
    protected static function redirect($type, $default_route = '/')
    {
        switch ($type)
        {
            case 'registration' :
                \Response::redirect(\Config::get('app.api._redirect.registration', $default_route));
            break;

            case 'after_login' :
                \Response::redirect(\Config::get('app.api._redirect.after_login', $default_route));
            break;

            default :
                throw new \Fuel_Exception("Unable to redirect type: {$type}");
                return;
        }

        return true;
    }
    
}