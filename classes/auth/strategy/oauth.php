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

use \OAuth\Consumer;
use \OAuth\Provider;

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
 * @category    Auth_Strategy_OAuth
 */

/**
 * Auth Strategy OAuth Class taken from NinjAuth Package for FuelPHP
 *
 * @package     NinjAuth
 * @author      Phil Sturgeon <https://github.com/philsturgeon>
 */

class Auth_Strategy_OAuth extends Auth_Strategy 
{
	public $provider;
	
	public function authenticate()
	{
		// Create an consumer from the config
		$consumer = Consumer::forge($this->config);
		
		// Load the provider
		$provider = Provider::forge($this->provider);
		
		// Create the URL to return the user to
		$callback = \Arr::get($this->config, 'callback') ?: \Uri::create(\Config::get('autho.urls.callback', \Request::active()->route->segments[0].'/callback'));
		$callback = rtrim($callback, '/').'/'.$this->provider;
		
		// Add the callback URL to the consumer
		$consumer->callback($callback); 

		// Get a request token for the consumer
		$token = $provider->request_token($consumer);

		// Store the token
		\Cookie::set('oauth_token', base64_encode(serialize($token)));

		// Redirect to the twitter login page
		\Response::redirect($provider->authorize_url($token, array(
			'oauth_callback' => $callback,
		)));
	}
	
	public function callback()
	{
		// Create an consumer from the config
		$this->consumer = Consumer::forge($this->config);

		// Load the provider
		$this->provider = Provider::forge($this->provider);
		
		if ($token = \Cookie::get('oauth_token'))
		{
			// Get the token from storage
			$this->token = unserialize(base64_decode($token));
		}
			
		if ($this->token and $this->token->access_token !== Input::get_post('oauth_token'))
		{   
			// Delete the token, it is not valid
			\Cookie::delete('oauth_token');

			// Send the user back to the beginning
			throw new Auth_Strategy_Exception('invalid token after coming back to site');
		}

		// Get the verifier
		$verifier = Input::get_post('oauth_verifier');

		// Store the verifier in the token
		$this->token->verifier($verifier);

		// Exchange the request token for an access token
		return $this->provider->access_token($this->consumer, $this->token);
	}
	
}