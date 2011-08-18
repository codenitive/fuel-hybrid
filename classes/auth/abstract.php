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

abstract class Auth_Abstract {

    /**
     * User data
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
        \Config::load('app', true);
        \Config::load('crypt', true);
    }

    /**
     * Return TRUE/FALSE whether visitor is logged in to the system
     * 
     * Usage:
     * 
     * <code>false === \Hybrid\Acl::instance()->is_logged()</code>
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
     * <code>$user = \Hybrid\Acl::instance()->get();</code>
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
                \Response::redirect(\Config::get('app.api._redirect.registration', $default_route));
            break;

            case 'after_login' :
                \Response::redirect(\Config::get('app.api._redirect.after_login', $default_route));
            break;

            case 'after_logout' :
                \Response::redirect(\Config::get('app.api._redirect.after_logout', $default_route));
            break;

            default :
                throw new \Fuel_Exception("Unable to redirect type: {$type}");
                return;
        }

        return true;
    }
    
}