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
 * @category    Auth_Facebook_Connection
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Facebook_Connection extends Auth_Connection {
    
    public static function factory()
    {
        return static::instance('facebook');
    }

    public function execute($items)
    {
        if (true !== $this->use_facebook) 
        {
            $this->unregister(true);
            throw new \Fuel_Exception('Please enable Facebook to use \\Hybrid\\Auth_Facebook_Query');
        }

        $auth   = \Hybrid\Auth::instance('facebook')->get();
        
        $query  = \DB::select('users.*', array('users_facebooks.id', 'facebook_id'), array('users_facebook.token', 'password_token'))
                    ->from('users')
                    ->join('users_facebooks')
                    ->on('users.id', '=', 'users_facebooks.user_id')
                    ->where('users_facebooks.facebook_id', '=', $auth->id)
                    ->limit(1);
        
        if (true === $this->use_auth)
        {
            $query->select('users_auths.password')
                ->join('users_auths')
                ->on('users_auths.user_id', '=', 'users.id');
        }
        
        if (true === $this->use_meta)
        {
            $query->select('users_meta.*')
                ->join('users_meta')
                ->on('users_meta.user_id', '=', 'users.id');    
        }
        
        $result = $results->as_object()->execute();

        $this->fetch_user($result);
        $this->fetch_role();

        return $this;
    }

    public function login($username, $token)
    {
        $query = \DB::select('users.*')
                ->from('users');

        if (true === $this->use_meta)
        {
            $query->select('users_meta.*')
                ->join('users_meta')
                ->on('users_meta.user_id', '=', 'users.id');    
        }

        $query->select(array('users_facebooks.id', 'facebook_id'), array('users_facebooks.token', 'password_token'))
                ->join('users_facebooks', 'left')
                ->on('users_facebooks.user_id', '=', 'users.id');

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
            throw new \Fuel_Exception("User {$username} does not exist in our database");
        }

        if ($this->items['password'] !== $token)
        {
            throw new \Fuel_Exception("Invalid Facebook token, please sign-in with Facebook again");
        }

        return $this;
    }

    public function logout()
    {
        $this->unregister();

        return $this;
    }
    
}