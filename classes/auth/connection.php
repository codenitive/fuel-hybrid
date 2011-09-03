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

Namespace Hybrid;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth_Connection
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Connection  {
    
    /**
     * Cache auth connection instance so we can reuse it on multiple request eventhough 
     * it's almost impossible to happen
     * 
     * @static
     * @access  protected
     * @var     array
     */
    protected static $instances = array();

    /**
     * Initiate a new Auth connection instance
     * 
     * @static
     * @access  public
     * @return  Auth_Connection
     * @throws  \Fuel_Exception
     */
    public static function forge($name = null)
    {
        if (\is_null($name))
        {
            $name = 'normal';
        }

        $name = \Str::lower($name);

        if (!isset(static::$instances[$name]))
        {
            $driver = '\\Hybrid\\Auth_' . ucfirst($name) . '_Connection';

            // instance has yet to be initiated
            if (\class_exists($driver))
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
        \Log::info("\Hybrid\Auth_Connection::factory() already deprecated, and staged to be removed in v1.3.0. Please use \Hybrid\Auth_Connection::forge().");
        
        return static::forge($name);
    }

    /**
     * Return instance (or create a new one if not available yet)
     *
     * @static
     * @access  public
     * @return  Auth_Connection
     * @see     self::forge
     */
    public static function instance($name = null)
    {
        return static::forge($name);
    }

    /**
     * User data
     *
     * @access  protected
     * @var     object|array
     */
    protected $items     = null;

    /**
     * Default value for user data
     * 
     * @access  protected
     * @return  bool
     */
    public function reset() 
    {
        $this->items = array(
            'id'        => 0,
            'user_name' => 'guest',
            'full_name' => '',
            'email'     => '',
            'roles'     => array('guest'),
            '_hash'     => null,
            'password'  => '',
            'method'    => 'normal',
            'gender'    => '',
            'status'    => 1,
        );

        return $this;
    }

    /**
     * List of user fields to be used
     *
     * @access  protected
     * @var     array
     */
    protected $optional_fields   = array('email', 'status', 'full_name', 'gender', 'birthdate');

    /**
     * Allow status to login based on `users`.`status`
     *
     * @access  protected
     * @var     array
     */
    protected $allowed_status    = array('verified');
    
    /**
     * Use `users_meta` table
     *
     * @access  protected
     * @var     bool
     */
     protected $use_meta          = true;

    /**
     * Use `users_auth` table
     *
     * @access  protected
     * @var     bool
     */
    protected $use_auth          = true;

    /**
     * Use Twitter OAuth
     *
     * @access  protected
     * @var     bool
     */
    protected $use_twitter       = false;

    /**
     * Use Facebook Connect
     *
     * @access  protected
     * @var     bool
     */
    protected $use_facebook      = false;

    /**
     * Load and bind instance configuration
     *
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        // load Auth configuration
        $config             = \Config::get('app.auth', \Config::get('app.user_table', array()));

        $reserved_property  = array('optional_fields');
        
        foreach ($config as $key => $value)
        {
            if (!\property_exists($this, "{$key}") or \in_array($key, $reserved_property))
            {
                continue;
            }

            $this->{$key} = $value;
            \Config::set("app.auth.{$key}", $value);
        }

        if (!isset($config['optional_fields']) or !\is_array($config['optional_fields']))
        {
            $config['optional_fields'] = array();
        }
        
        $this->optional_fields = \array_merge($config['optional_fields'], $this->optional_fields);

        foreach ($this->optional_fields as $field)
        {
            if (\is_string($field) and !isset($this->items[$field]))
            {
                $this->items[$field] = '';
            }
        }
    }

    /**
     * Return TRUE/FALSE whether visitor is logged in to the system
     * 
     * Usage:
     * 
     * <code>false === \Hybrid\Auth_Connection::instance()->is_logged()</code>
     *
     * @access  public
     * @return  bool
     */
    public function is_logged() 
    {
        return ($this->items['id'] > 0 ? true : false);
    }

    /**
     * Get current user authentication
     * 
     * Usage:
     * 
     * <code>$user = \Hybrid\Auth_Connection::instance()->get();</code>
     *
     * @access  public
     * @param   string  $name optional key value, return all if $name is null
     * @return  object
     */
    public function get($name = null) 
    {
        if (\is_null($name)) 
        {
            return (object) $this->items;
        }

        if (\array_key_exists($name, $this->items)) 
        {
            return $this->items[$name];
        }

        return null;
    }

    /**
     * Get user's data
     *
     * @access  protected
     * @param   object  $result     Database result object
     * @return  void
     */
    protected function fetch_user($result)
    {
        if (\is_null($result) or $result->count() < 1) 
        {
            $this->reset();
        } 
        else 
        {
            $user = $result->current();

            if (!\in_array($user->status, $this->allowed_status)) 
            {
                // only verified user can login to this application
                $this->reset();
            }

            // we validate the hash to add security to this application
            $hash = $user->user_name . $user->password_token;

            if (!is_null($this->items['_hash']) and $this->items['_hash'] !== \Hybrid\Auth::add_salt($hash)) 
            {
                $this->reset();
            }

            $this->items['id']        = $user->user_id;
            $this->items['user_name'] = $user->user_name;
            $this->items['password']  = $user->password_token;
            
            foreach ($this->optional_fields as $property)
            {
                if (!\property_exists($user, $property))
                {
                    continue;
                }
                    
                $this->items[$property]   = $user->{$property};
            }
        }
    }

    /**
     * Get user's roles
     * 
     * @access  protected
     * @return  bool
     */
    protected function fetch_role() 
    {
        $data = array();

        $roles = \DB::select('roles.id', 'roles.name')
                ->from('roles')
                ->join('users_roles')
                ->on('users_roles.role_id', '=', 'roles.id')
                ->where('users_roles.user_id', '=', $this->items['id'])
                ->as_object()
                ->execute();

        foreach ($roles as $role) 
        {
            $data['' . $role->id]   = \Inflector::friendly_title($role->name, '-', true);
        }

        $this->items['roles']     = $data;

        return true;
    }

    /**
     * Register user's authentication to Session
     *
     * @access  protected
     * @return  bool
     */
    protected function register() 
    {
        $values             = $this->items;
        $hash               = $values['user_name'] . $values['password'];
        $values['_hash']    = \Hybrid\Auth::add_salt($hash);

        unset($values['password']);

        \Cookie::set('_users', \Crypt::encode(\serialize((object) $values)));

        return true;
    }

    /**
     * Delete user's authentication
     *
     * @access  protected
     * @param   bool    $delete     set to true to delete session, only when login out
     * @return  bool
     */
    protected function unregister($delete = false) 
    {
        $this->reset();

        if (false === $delete) 
        {
            return true;
        }

        \Cookie::delete('_users');
    }

}