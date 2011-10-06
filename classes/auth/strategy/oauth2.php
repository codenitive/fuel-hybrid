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
 * @category    Auth_Strategy_OAuth2
 * @author      Phil Sturgeon <https://github.com/philsturgeon>
 */

class Auth_Strategy_OAuth2 extends Auth_Strategy {
    
    public $provider;
    
    public function authenticate()
    {
        // Load the provider
        $provider = \OAuth2\Provider::factory($this->provider, $this->config);
        
        $provider->authorize(array(
            'redirect_uri' => \Uri::create(\Config::get('autho.urls.callback', \Request::active()->route->segments[0].'/callback').'/'.$this->provider)
        ));
    }
    
    public function callback()
    {
        // Load the provider
        $this->provider = \OAuth2\Provider::factory($this->provider, $this->config);
        
        try
        {
            $params = $this->provider->access(\Input::get('code'));
            
            return (object) array(
                'token' => $params['access_token'],
                'secret' => null,
            );
        }
    
        catch (Exception $e)
        {
            exit('That didnt work: '.$e);
        }
    }
    
}