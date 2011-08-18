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
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth_Driver
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Auth_Driver {

    /**
     * Redirect user based on type
     *
     * @static
     * @access  protected
     * @param   string  $type
     * @param   string  $default_route
     * @return  void
     * @throws  \Fuel_Exception
     */
    protected static function redirect($type, $default_route = '/')
    {
        switch ($type)
        {
            case 'registration' :
                \Response::redirect(\Config::get('app._route_.registration', $default_route));
            break;

            case 'after_login' :
                \Response::redirect(\Config::get('app._route_.after_login', $default_route));
            break;

            case 'after_logout' :
                \Response::redirect(\Config::get('app._route_.after_logout', $default_route));
            break;

            default :
                throw new \Fuel_Exception("Unable to redirect type: {$type}");
                return;
        }

        return true;
    }

    /**
     * Adapter object
     *
     * @access  protected
     * @var     object
     */
    protected $adapter   = null;

    /**
     * Auth data
     *
     * @static
     * @access  protected
     * @var     object|array
     */
    protected $auth     = null;

    /**
     * Load configurations
     *
     * @static 
     * @access  public
     * @return  void
     */
    protected function _initiate()
    {
        \Config::load('app', 'app');
        \Config::load('crypt', true);
    }

    /**
     * Return Adapter Object
     *
     * @access  public
     * @return  object
     */
    public function get_adapter() 
    {
        return $this->adapter;
    }

    /**
     * Return TRUE/FALSE whether visitor is logged in to the system
     * 
     * Usage:
     * 
     * <code>false === \Hybrid\Auth::instance()->is_logged()</code>
     *
     * @access  public
     * @return  bool
     */
    public function is_logged() 
    {
        return ($this->auth['id'] > 0 ? true : false);
    }

    /**
     * Get current user authentication
     * 
     * Usage:
     * 
     * <code>$user = \Hybrid\Auth::instance()->get();</code>
     *
     * @access  public
     * @param   string  $name optional key value, return all if $name is null
     * @return  object
     */
    public function get($name = null) 
    {
        if (\is_null($name)) 
        {
            return (object) $this->auth;
        }

        if (\array_key_exists($name, $this->auth)) 
        {
            return $this->auth[$name];
        }

        return null;
    }
    
}