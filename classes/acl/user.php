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
 * @category    Acl_User
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Acl_User extends Acl_Abstract {

    protected static $items     = null;
    public static $acl          = NULL;

    /**
     * Default value for user data
     * 
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function set_default() 
    {
        static::$items = array(
            'id'        => 0,
            'user_name' => 'guest',
            'full_name' => '',
            'email'     => '',
            'roles'     => array('guest'),
            '_hash'     => '',
            'password'  => '',
            'method'    => 'normal',
            'gender'    => '',
            'status'    => 1,
            'twitter'   => 0,
            'facebook'  => 0
        );

        return true;
    }
    
    protected static $optional_fields   = array('email', 'status', 'full_name', 'gender', 'birthdate');
    protected static $allowed_status    = array('verified');
    protected static $use_meta          = true;
    protected static $use_auth          = true;
    protected static $use_twitter       = false;
    protected static $use_facebook      = false;

    /**
     * Get Acl\Role object, it's a quick way of get and use \Acl\Role without having to 
     * initiate another call when this class already has it
     * 
     * Usage:
     * 
     * <code>$role = \Hybrid\Acl_User::acl();
     * $role->add_recources('monkeys');</code>
     * 
     * @static
     * @access  public
     * @return  object
     */
    public static function acl() 
    {
        return static::$acl;
    }

    /**
     * Initiate and check user authentication, the method will try to detect current 
     * cookie for this session and verify the cookie with the database, it has to 
     * be verify so that no one else could try to copy the same cookie configuration 
     * and use it as their own.
     * 
     * @todo    need to use User-Agent as one of the hash value 
     * 
     * @static
     * @access  private
     * @return  bool
     */
    public static function _init() 
    {
        parent::_init();

        // allow to disable user acl, would be useful when database not available
        if (false === \Config::get('app.user_acl.enabled', true))
        {
            return;
        }
        
        // This method should only be called once, but just in case that doesn't work we should return null
        if (!\is_null(static::$acl))
        {
            return;
        }

        // get user data from cookie
        $users              = \Cookie::get('_users');

        // user data shouldn't be null if there user authentication available, if not populate from default
        if (!is_null($users)) 
        {
            $users          = \unserialize(\Crypt::decode($users));
            static::$items  = (array) $users;
        } 
        else 
        {
            $users          = new \stdClass();
            $users->method  = 'normal';
            static::unregister();
        }

        // this is optional, but useful as a shorthand
        static::$acl        = new \Hybrid\Acl;

        static::load_config();

        switch ($users->method) 
        {
            case 'normal' :

                $results = \DB::select('users.*')
                    ->from('users')
                    ->where('users.id', '=', static::$items['id'])
                    ->limit(1);
                
                if (true === static::$use_auth)
                {
                    $results->select('users_auths.password')
                        ->join('users_auths')
                        ->on('users_auths.user_id', '=', 'users.id');
                }
                
                if (true === static::$use_meta)
                {
                    $results->select('users_meta.*')
                        ->join('users_meta')
                        ->on('users_meta.user_id', '=', 'users.id');    
                }
                
                if (true === static::$use_twitter)
                {
                    $results->select(array('users_twitters.id', 'twitter_id'))
                        ->join('users_twitters', 'left')
                        ->on('users_twitters.user_id', '=', 'users.id');
                }

                if (true === static::$use_facebook)
                {
                    $results->select(array('users_facebooks.id', 'facebook_id'))
                        ->join('users_facebooks', 'left')
                        ->on('users_facebooks.user_id', '=', 'users.id');
                }
                
                $result = $results->as_object()->execute();

            break;

            case 'twitter_oauth' :
                if (true !== static::$use_twitter) 
                {
                    static::unregister(true);
                    return true;
                }
                
                $twitter_oauth = \Hybrid\Acl_Twitter::get();
                
                $results = \DB::select('users.*', array('users_twitters.id', 'twitter_id'))
                    ->from('users')
                    ->join('users_twitters')
                    ->on('users.id', '=', 'users_twitters.user_id')
                    ->where('users_twitters.twitter_id', '=', $twitter_oauth->id)
                    ->limit(1);
                
                if (true === static::$use_auth)
                {
                    $results->select('users_auths.password')
                        ->join('users_auths')
                        ->on('users_auths.user_id', '=', 'users.id');
                }
                
                if (true === static::$use_meta)
                {
                    $results->select('users_meta.*')
                        ->join('users_meta')
                        ->on('users_meta.user_id', '=', 'users.id');    
                }

                if (true === static::$use_facebook)
                {
                    $results->select(array('users_facebooks.id', 'facebook_id'))
                        ->join('users_facebooks', 'left')
                        ->on('users_facebooks.user_id', '=', 'users.id');
                }
                
                $result = $results->as_object()->execute();
            break;

            case 'facebook_oauth' :
                if (true !== static::$use_facebook) 
                {
                    static::unregister(true);
                    return true;
                }
                
                $facebook_oauth = \Hybrid\Acl_Facebook::get();
                
                $results = \DB::select('users.*', array('users_facebooks.id', 'facebook_id'))
                    ->from('users')
                    ->join('users_facebooks')
                    ->on('users.id', '=', 'users_facebooks.user_id')
                    ->where('users_facebooks.facebook_id', '=', $facebook_oauth->id)
                    ->limit(1);
                
                if (true === static::$use_auth)
                {
                    $results->select('users_auths.password')
                        ->join('users_auths')
                        ->on('users_auths.user_id', '=', 'users.id');
                }
                
                if (true === static::$use_meta)
                {
                    $results->select('users_meta.*')
                        ->join('users_meta')
                        ->on('users_meta.user_id', '=', 'users.id');    
                }

                if (true === static::$use_twitter)
                {
                    $results->select(array('users_twitters.id', 'twitter_id'))
                        ->join('users_twitters', 'left')
                        ->on('users_twitters.user_id', '=', 'users.id');
                }
                
                $result = $results->as_object()->execute();
            break;
        }

        if (\is_null($result) or $result->count() < 1) 
        {
            static::unregister(true);
            return true;
        } 
        else 
        {
            $user = $result->current();

            if (!\in_array($user->status, static::$allowed_status)) 
            {
                // only verified user can login to this application
                static::unregister();
                return true;
            }

            // we validate the hash to add security to this application
            $hash = $user->user_name . $user->password;

            if (static::$items['_hash'] !== static::add_salt($hash)) 
            {
                static::unregister();
                return true;
            }

            static::$items['id']        = $user->user_id;
            static::$items['user_name'] = $user->user_name;
            static::$items['roles']     = $users->roles;
            static::$items['password']  = $user->password;
            
            foreach (static::$optional_fields as $property)
            {
                if (!\property_exists($user, $property))
                {
                    continue;
                }
                    
                static::$items[$property]   = $user->{$property};
            }

            // if user already link their account with twitter, map the relationship
            if (\property_exists($user, 'twitter_id')) 
            {
                static::$items['twitter']   = $user->twitter_id;
            }

            // if user already link their account with facebook, map the relationship
            if (\property_exists($user, 'facebook_id')) 
            {
                static::$items['facebook']  = $user->facebook_id;
            }
        }

        return true;
    }

    /**
     * Load and bind instance configuration
     *
     * @static
     * @access  protected
     * @return  void
     */
    protected static function load_config()
    {
        // load ACL configuration
        $config             = \Config::get('app.user_acl', array());

        $reserved_property  = array('items', 'acl', 'optional_fields');
        
        foreach ($config as $key => $value)
        {
            if (!\property_exists('\\Hybrid\\Acl_User', "{$key}") or \in_array($key, $reserved_property))
            {
                continue;
            }

            static::$$key = $value;
            \Config::set("app.user_acl.{$key}", $value);
        }

        if (!isset($config['optional_fields']) or !\is_array($config['optional_fields']))
        {
            $config['optional_fields'] = array();
        }
        
        static::$optional_fields = \array_merge($config['optional_fields'], static::$optional_fields);

        foreach (static::$optional_fields as $field)
        {
            if (\is_string($field) and !isset(static::$items[$field]))
            {
                static::$items[$field] = '';
            }
        }

        return true;
    }

    /**
     * Login user using normal authentication (username and password)
     * 
     * Usage:
     * 
     * <code>$login = \Hybrid\Acl_User::login('someone', 'password');</code>
     * 
     * @static
     * @access  public
     * @param   string  $username
     * @param   string  $password
     * @return  bool
     */
    public static function login($username, $password, $method = 'normal') 
    {
        $result = \DB::select('users.*')
                ->from('users');
        
        if (true === static::$use_auth)
        {
            $result->select('users_auths.password')
                ->join('users_auths')
                ->on('users_auths.user_id', '=', 'users.id');
        }

        if (true === static::$use_meta)
        {
            $result->select('users_meta.*')
                ->join('users_meta')
                ->on('users_meta.user_id', '=', 'users.id');    
        }

        if (true === static::$use_twitter)
        {
            $result->select(array('users_twitters.id', 'twitter_id'), array('users_twitters.token', 'twitter_token'))
                ->join('users_twitters', 'left')
                ->on('users_twitters.user_id', '=', 'users.id');
        }

        if (true === static::$use_facebook)
        {
            $result->select(array('users_facebooks.id', 'facebook_id'), array('users_facebooks.token', 'facebook_token'))
                ->join('users_facebooks', 'left')
                ->on('users_facebooks.user_id', '=', 'users.id');
        }

        $result = $result->where_open()
            ->where('users.user_name', '=', $username)
            ->or_where('users.email', '=', $username)
            ->where_close()
            ->limit(1)
            ->as_object()
            ->execute();

        if (\is_null($result) or $result->count() < 1) 
        {
            throw new \Fuel_Exception("User {$username} does not exist in our database");
            return false;
        } 
        else 
        {
            $user = $result->current();

            switch ($method)
            {
                case 'normal' :
                    if ($user->password !== static::add_salt($password)) 
                    {
                        throw new \Fuel_Exception("Invalid username and password combination");
                        return false;
                    }
                break;

                case 'twitter_oauth' :
                    if ($user->twitter_token !== $password)
                    {
                        throw new \Fuel_Exception("Invalid Twitter token, please sign-in with Twitter again");
                        return false;
                    }
                break;

                case 'facebook_oauth' :
                    if ($user->facebook_token !== $password)
                    {
                        throw new \Fuel_Exception("Invalid Facebook token, please sign-in with Facebook again");
                        return false;
                    }
                break;
            }

            if (!\in_array($user->status, static::$allowed_status)) 
            {
                throw new \Fuel_Exception("User {$username} is not allowed to login");
                return false;
            }

            static::$items['id']        = $user->user_id;
            static::$items['user_name'] = $user->user_name;
            static::$items['method']    = $method;
            static::$items['password']  = $user->password;
            
            foreach (static::$optional_fields as $property)
            {
                if (!\property_exists($user, $property))
                {
                    continue;
                }
                
                static::$items[$property] = $user->{$property};
            }

            // if user already link their account with twitter, map the relationship
            if (\property_exists($user, 'twitter_id')) 
            {
                static::$items['twitter']   = $user->twitter_id;
            }

            // if user already link their account with facebook, map the relationship
            if (\property_exists($user, 'facebook_id')) 
            {
                static::$items['facebook']  = $user->facebook_id;
            }

            static::get_roles();
            static::register();

            return true;
        }

        return false;
    }

    /**
     * Initiate user login out regardless of any method they use
     *
     * Usage:
     * 
     * <code>\Hybrid\Acl_User::logout(false);</code>
     * 
     * @static
     * @access  public
     * @param   bool    $redirect
     * @return  bool
     */
    public static function logout($redirect = true) 
    {
        static::unregister(true);

        if (true === $redirect) 
        {
            static::redirect('after_login');
        }

        return true;
    }

    /**
     * Get user's roles
     * 
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function get_roles() 
    {
        $data = array();

        $roles = \DB::select('roles.id', 'roles.name')
                ->from('roles')
                ->join('users_roles')
                ->on('users_roles.role_id', '=', 'roles.id')
                ->where('users_roles.user_id', '=', static::$items['id'])
                ->as_object()
                ->execute();

        foreach ($roles as $role) 
        {
            $data['' . $role->id]   = \Inflector::friendly_title($role->name, '-', true);
        }

        static::$items['roles']     = $data;

        return true;
    }

    /**
     * Register user's authentication to Session
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function register() 
    {
        $values             = static::$items;
        $values['_hash']    = static::add_salt(static::$items['user_name'] . static::$items['password']);

        \Cookie::set('_users', \Crypt::encode(\serialize((object) $values)));

        return true;
    }

    /**
     * Delete user's authentication
     *
     * @static
     * @access  protected
     * @param   bool    $delete set to true to delete session, only when login out
     * @return  bool
     */
    protected static function unregister($delete = false) 
    {
        static::set_default();

        if (false === $delete) 
        {
            return true;
        }

        \Cookie::delete('_users');

        if (true === static::$use_twitter)
        {
            \Hybrid\Acl_Twitter::logout();
        }

        if (true === static::$use_facebook)
        {
            \Hybrid\Acl_Facebook::logout(false);
        }

        return true;
    }

}