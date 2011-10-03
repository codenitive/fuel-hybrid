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
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth_Provider_Normal
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Provider_Normal {

    public $data = null;
     /**
     * List of user fields to be used
     *
     * @access  protected
     * @var     array
     */
    protected $optional_fields = array('status', 'full_name');
     
     /**
     * Allow status to login based on `users`.`status`
     *
     * @access  protected
     * @var     array
     */
    protected $allowed_status = array('verified');
     
     /**
     * Use `users_meta` table
     *
     * @access  protected
     * @var     bool
     */
    protected $use_meta = true;
     
     /**
     * Use `users_auth` table
     *
     * @access  protected
     * @var     bool
     */
    protected $use_auth = true;

    /**
     * Verify User Agent in Hash
     * 
     * @access  protected
     * @var     bool
     */
    protected $verify_user_agent = false;


    public static function forge()
    {
        return new static();
    }

    public static function factory()
    {
        \Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
        return static::forge();
    }

    protected function __construct()
    {
        $this->reset();

        // load Auth configuration
        $config            = \Config::get('autho.normal', array());
        
        $reserved_property = array('optional_fields');
        
        foreach ($config as $key => $value)
        {
            if (!property_exists($this, $key) or in_array($key, $reserved_property))
            {
                continue;
            }

            if (is_null($value))
            {
                continue;
            }

            $this->{$key} = $value;
            \Config::set("autho.normal.{$key}", $value);
        }

        if (!isset($config['optional_fields']) or !is_array($config['optional_fields']))
        {
            $config['optional_fields'] = array();
        }
        
        $this->optional_fields = array_merge($config['optional_fields'], $this->optional_fields);

        foreach ($this->optional_fields as $field)
        {
            if (is_string($field) and !isset($this->items[$field]))
            {
                $this->data[$field] = '';
            }
        }

        $this->verify_user_agent = \Config::get('autho.verify_user_agent', $this->verify_user_agent);
    }

    /**
     * Default value for user data
     * 
     * @access  protected
     * @return  bool
     */
    public function reset() 
    {
        $this->data = array(
            'id'        => 0,
            'user_name' => 'guest',
            'full_name' => '',
            'email'     => '',
            '_hash'     => null,
            'password'  => '',
            'method'    => 'normal',
            'gender'    => '',
            'status'    => null,
            'roles'     => array('0' => 'guest'),
            'accounts'  => array(),
        );

        return $this;
    }

    public function get()
    {
        return $this->data;
    }

    public function access_token($data)
    {
        $this->data['_hash'] = '';

        if (isset($data['_hash']))
        {
            $this->data['_hash'] = $data['_hash'];
        }

        $query = \DB::select('users.*')
            ->from('users')
            ->where('users.id', '=', $data['id'])
            ->limit(1);
        
        if (true === $this->use_auth)
        {
            $query->select(array('users_auths.password', 'password_token'))
                ->join('users_auths')
                ->on('users_auths.user_id', '=', 'users.id');
        }
        else
        {
            $query->select(array('users.password', 'password_token'));
        }
        
        if (true === $this->use_meta)
        {
            $query->select('users_meta.*')
                ->join('users_meta')
                ->on('users_meta.user_id', '=', 'users.id');    
        }
        
        $result = $query->as_object()->execute();

        $this->fetch_user($result);

        $this->fetch_linked_roles();
        $this->fetch_linked_accounts();

        $this->verify_token();

        return $this;
    }

    public function login($username, $password)
    {
        $query = \DB::select('users.*')
                ->from('users');
        
        if (true === $this->use_auth)
        {
            $query->select(array('users_auths.password', 'password_token'))
                ->join('users_auths')
                ->on('users_auths.user_id', '=', 'users.id');
        }
        else
        {
            $query->select(array('users.password', 'password_token'));
        }

        if (true === $this->use_meta)
        {
            $query->select('users_meta.*')
                ->join('users_meta')
                ->on('users_meta.user_id', '=', 'users.id');    
        }

        $result = $query->where_open()
            ->where('users.user_name', '=', $username)
            ->or_where('users.email', '=', $username)
            ->where_close()
            ->limit(1)
            ->as_object()
            ->execute();

        $this->fetch_user($result);

        $this->fetch_linked_roles();
        $this->fetch_linked_accounts();

        if ($this->data['id'] < 1)
        {
            $this->reset();
            throw new \Fuel_Exception("User {$username} does not exist in our database");
        }

        if ($this->data['password'] !== Auth::add_salt($password))
        {
            $this->reset();
            throw new \Fuel_Exception("Invalid username and password combination");
        }

        $this->verify_token();

        return $this;
    }

    public function login_token($token, $secret)
    {
        $query = \DB::select('users.*')
            ->from('users')
            ->join('authentications')
            ->on('authentications.user_id', '=', 'users.id')
            ->where('authentications.token', '=', $token)
            ->where('authentications.secret', '=', $secret);
        
        if (true === $this->use_auth)
        {
            $query->select(array('users_auths.password', 'password_token'))
                ->join('users_auths')
                ->on('users_auths.user_id', '=', 'users.id');
        }
        else
        {
            $query->select(array('users.password', 'password_token'));
        }

        if (true === $this->use_meta)
        {
            $query->select('users_meta.*')
                ->join('users_meta')
                ->on('users_meta.user_id', '=', 'users.id');    
        }

        $result = $query->limit(1)
            ->as_object()
            ->execute();

        $this->fetch_user($result);

        $this->fetch_linked_roles();
        $this->fetch_linked_accounts();

        if ($this->data['id'] < 1)
        {
            throw new \Fuel_Exception("User does not exist in our database");
        }

        $this->verify_token();

        return $this;
    }

    public function logout()
    {
        $this->revoke_token(true);
        return $this;
    }

    /**
     * Register user's authentication to Session
     *
     * @access  protected
     * @return  bool
     */
    protected function verify_token()
    {
        $values = $this->data;
        $hash   = $values['user_name'] . $values['password'];

        if ($this->verify_user_agent)
        {
            $hash .= Input::user_agent();
        }

        $values['_hash'] = Auth::add_salt($hash);

        unset($values['password']);

        \Cookie::set('_users', \Crypt::encode(serialize((object) $values)));

        return true;
    }

    /**
     * Delete user's authentication
     *
     * @access  protected
     * @param   bool    $delete     set to true to delete session, only when login out
     * @return  bool
     */
    protected function revoke_token($delete = false)
    {
        $this->reset();

        if (true === $delete) 
        {
            \Cookie::delete('_users');
        }
        
        return true;
    }

    protected function fetch_user($result)
    {
        if (is_null($result) or $result->count() < 1) 
        {
            return $this->reset();
        } 
    
        $user = $result->current();

        if (!in_array($user->status, $this->allowed_status)) 
        {
            // only verified user can login to this application
            return $this->reset();
        }

        // we validate the hash to add security to this application
        $hash = $user->user_name . $user->password_token;

        if ($this->verify_user_agent)
        {
            $hash .= Input::user_agent();
        }

        if (!is_null($this->data['_hash']) and $this->data['_hash'] !== Auth::add_salt($hash)) 
        {
            return $this->reset();
        }

        $this->data['id']        = $user->user_id;
        $this->data['user_name'] = $user->user_name;
        $this->data['email']     = $user->email;
        $this->data['password']  = $user->password_token;
        
        foreach ($this->optional_fields as $property)
        {
            if (!property_exists($user, $property))
            {
                continue;
            }
                
            $this->data[$property] = $user->{$property};
        }
    }

    protected function fetch_linked_roles()
    {
        $data  = array();
        
        $roles = \DB::select('roles.id', 'roles.name')
            ->from('roles')
            ->join('users_roles')
            ->on('users_roles.role_id', '=', 'roles.id')
            ->where('users_roles.user_id', '=', $this->data['id'])
            ->as_object()
            ->execute();

        foreach ($roles as $role) 
        {
            $data['' . $role->id] = \Inflector::friendly_title($role->name, '-', true);
        }
            
        $this->data['roles'] = $data;

        return true;
    }

    protected function fetch_linked_accounts()
    {
        $data = array();
        
        $accounts = \DB::select('*')
            ->from('authentications')
            ->where('user_id', '=', $this->data['id'])
            ->as_object()
            ->execute();

        foreach ($accounts as $account) 
        {
            $data['' . $account->provider] = array(
                'token'  => $account->token,
                'secret' => $account->secret,
            );
        }
            
        $this->data['accounts'] = $data;

        return true;
    }

}