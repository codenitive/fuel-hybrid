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
 * @category    Auth_User
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_User extends Auth_Driver {

    /**
     * method or connection used, default to 'normal'
     *
     * @access  protected
     * @var     string
     */
    protected $method   = 'normal';
    
    /**
     * Adapter to \Hybrid\Acl
     *
     * @access  public
     * @var     object
     */
    public $acl          = null;

    /**
     * Get Acl\Role object, it's a quick way of get and use \Acl\Role without having to 
     * initiate another call when this class already has it
     * 
     * Usage:
     * 
     * <code>$role = \Hybrid\Auth::instance('user')->acl();
     * $role->add_recources('monkeys');</code>
     * 
     * @access  public
     * @return  object
     */
    public function acl() 
    {
        return $this->acl;
    }

     /**
     * Get self instance from cache instead of initiating a new object if time 
     * we need to use this object
     *
     * @static
     * @access  public
     * @return  self
     */
    public static function instance()
    {
        return \Hybrid\Auth::instance('user');
    }

    /**
     * Initiate and check user authentication, the method will try to detect current 
     * cookie for this session and verify the cookie with the database, it has to 
     * be verify so that no one else could try to copy the same cookie configuration 
     * and use it as their own.
     * 
     * @todo    need to use User-Agent as one of the hash value 
     * 
     * @access  private
     * @return  bool
     */
    public function __construct() 
    {
        parent::_initiate();

        // allow to disable user acl, would be useful when database not available
        if (false === \Config::get('app.auth.enabled', true))
        {
            return;
        }
        
        // This method should only be called once, but just in case that doesn't work we should return null
        if (!\is_null($this->acl))
        {
            return;
        }

        // get user data from cookie
        $users              = \Cookie::get('_users');

        // user data shouldn't be null if there user authentication available, if not populate from default
        if (!\is_null($users)) 
        {
            $users          = \unserialize(\Crypt::decode($users));
            $this->method   = (isset($users->method) ? $users->method : 'normal');
        }
        else
        {
            $users          = new \stdClass();
            $users->id      = 0;
            $users->_hash   = '';
        }

        $this->adapter      = \Hybrid\Auth_Connection::instance($this->method)->execute((array) $users);
    }

    /**
     * Login user using normal authentication (username and password)
     * 
     * Usage:
     * 
     * <code>$login = \Hybrid\Auth::instance('user')->login('someone', 'password');</code>
     * 
     * @access  public
     * @param   string  $username
     * @param   string  $password
     * @return  bool
     * @throws  \Fuel_Exception
     */
    public function login($username, $password) 
    {
        $this->adapter = \Hybrid\Auth_Connection::instance('normal')->login($username, $password);

        return true;
    }

     /**
     * Return TRUE/FALSE whether visitor is logged in to the system
     * 
     * Usage:
     * 
     * <code>false === \Hybrid\Auth::instance('user')->is_logged()</code>
     *
     * @access  public
     * @return  bool
     */
    public function is_logged() 
    {
        return $this->adapter->is_logged();
    }

    /**
     * Get current user authentication
     * 
     * Usage:
     * 
     * <code>$user = \Hybrid\Auth::instance('user')->get();</code>
     *
     * @access  public
     * @param   string  $name optional key value, return all if $name is null
     * @return  object
     */
    public function get($name = null) 
    {
        return $this->adapter->get($name);
    }

    /**
     * Initiate user login out regardless of any method they use
     *
     * Usage:
     * 
     * <code>\Hybrid\Auth::instance('user')->logout(false);</code>
     * 
     * @access  public
     * @param   bool    $redirect
     * @return  bool
     */
    public function logout($redirect = true) 
    {
        $this->adapter->logout();

        if (true === $redirect) 
        {
            static::redirect('after_logout');
        }

        return true;
    }

}