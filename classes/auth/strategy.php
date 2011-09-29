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
 * @category    Auth_Strategy
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Auth_Strategy {

    public $provider = null;
    public $config   = array();
    public $name     = null;
    
    protected static $providers = array(
        'normal'   => 'Normal',
        'facebook' => 'OAuth2',
        'twitter'  => 'OAuth',
        'dropbox'  => 'OAuth',
        'flickr'   => 'OAuth',
        'google'   => 'OAuth',
        'github'   => 'OAuth2',
        'linkedin' => 'OAuth',
        'youtube'  => 'OAuth',
    );

    public function __construct($provider)
    {
        $this->provider = $provider;
        
        $this->config   = \Config::get("autho.providers.{$provider}");
        
        if (is_null($this->name))
        {
            // Attempt to guess the name from the class name
            $this->name = strtolower(str_replace('\Hybrid\Auth_Strategy_', '', get_class($this)));
        }
    }

    public static function factory($provider)
    {
        $strategy = \Arr::get(static::$providers, $provider);
        
        if (!$strategy)
        {
            throw new \Fuel_Exception(sprintf('Provider "%s" has no strategy.', $provider));
        }
        
        $class = "Auth_Strategy_{$strategy}";
        return new $class($provider);
    }

    public static function login_or_register($strategy)
    {
        $response = $strategy->callback();
        
        if (true === Auth::instance('user')->is_logged())
        {
            // User already logged in 
            $user_id    = Auth::instance('user')->get('id');
            
            $accounts   = Auth::instance('user')->get('accounts');
            
            $num_linked = count($accounts);
        
            // Allowed multiple providers, or not authed yet?
            if ($num_linked === 0 or \Config::get('autho.link_multiple_providers') === true)
            {
                switch ($strategy->name)
                {
                    case 'oauth':
                        $user_hash = $strategy->provider->get_user_info($strategy->consumer, $response);
                    break;

                    case 'oauth2':
                        $user_hash = $strategy->provider->get_user_info($response->token);
                    break;
                }

                Auth::instance('user')->link_account($user_hash);

                // Attachment went ok so we'll redirect
                Auth::redirect('logged_in');
            }
            else
            {
                $providers = array_keys($accounts);

                throw new \Fuel_Exception(sprintf('This user is already linked to "%s".', $providers[0]));
            }
        }
        // The user exists, so send him on his merry way as a user
        else 
        {
            try 
            {
                Auth::instance('user')->login_token($response->token, $response->secret);
                // credentials ok, go right in
                Auth::redirect('logged_in');
            }
            catch (\Fuel_Exception $e)
            {
                switch ($strategy->name)
                {
                    case 'oauth':
                        $user_hash = $strategy->provider->get_user_info($strategy->consumer, $response);
                    break;
                    
                    case 'oauth2':
                        $user_hash = $strategy->provider->get_user_info($response->token);
                    break;
                    
                    default:
                        exit('Ummm....');
                }
                
                \Session::set('autho', $user_hash);

                Auth::redirect('registration');
            }
        }
    }

    abstract public function authenticate();

}