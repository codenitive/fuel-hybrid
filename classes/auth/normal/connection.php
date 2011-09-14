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
 * @category    Auth_Normal_Connection
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Normal_Connection extends Auth_Connection {
    
    /**
     * Execute to fetch user information using Facebook Auth
     *
     * @access  public
     * @param   array   $items      self::items value retrieved from Cookie
     * @return  self
     */
    public function execute($items)
    {
        if (isset($items['_hash']))
        {
            $this->items['_hash'] = $items['_hash'];
        }
        
        $query  = \DB::select('users.*')
                    ->from('users')
                    ->where('users.id', '=', $items['id'])
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
        
        $result     = $query->as_object()->execute();

        $this->fetch_user($result);
        $this->fetch_role();

        $this->register();

        return $this;
    }

    /**
     * Login using Normal Login
     *
     * @access  public
     * @param   string  $username   application username or email address
     * @param   string  $password   Raw user password, to be hashed and compared using \Hybrid\Auth::add_salt()
     * @return  self
     */
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
        $this->fetch_role();

        if ($this->items['id'] < 1)
        {
            throw new \Fuel_Exception("User {$username} does not exist in our database.");
        }

        if ($this->items['password'] !== \Hybrid\Auth::add_salt($password))
        {
            throw new \Fuel_Exception("Invalid username and password combination.");
        }

        $this->register();

        return $this;
    }

    /**
     * Logout and remove Cookie
     *
     * @access  public
     * @return  self
     */
    public function logout()
    {
        $this->unregister(true);

        return $this;
    }
    
}