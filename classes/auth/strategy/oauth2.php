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

use OAuth2\Provider;

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
 */

 /**
 * Auth Strategy OAuth2 Class taken from NinjAuth Package for FuelPHP
 *
 * @package     NinjAuth
 * @author      Phil Sturgeon <https://github.com/philsturgeon>
 */

class Auth_Strategy_OAuth2 extends Auth_Strategy 
{
	public $provider;
	
	public function authenticate()
	{
		// Load the provider
		$provider = Provider::forge($this->provider, $this->config);

		// Grab a callback from the config

		if ($provider->callback === null)
		{
			$provider->callback = \Uri::create(\Config::get('autho.urls.callback', \Request::active()->route->segments[0].'/callback'));
			$provider->callback = rtrim($provider->callback, '/').'/'.$this->provider;
		}
		
		$provider->authorize(array(
			'redirect_uri' => $provider->callback,
		));
	}
	
	public function callback()
	{
		// Load the provider
		$this->provider = Provider::forge($this->provider, $this->config);
		
		$error = Input::get('error');

		if (null !== $error)
		{
			throw new Auth_Strategy_Exception(ucfirst($this->provider->name)." Error: ".$error);
		}

		$code = Input::get('code');

		if (null === $code or empty($code))
		{
			// Send the user back to the beginning
			throw new Auth_Strategy_Exception('invalid token after coming back to site');
		}

		try
		{
			return $this->provider->access($code);
		}
		catch (Exception $e)
		{
			throw new Auth_Strategy_Exception('That didnt work: '.$e);
		}
	}
	
}