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
 * @category    Auth_Strategy_Normal
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Strategy_Normal extends Auth_Strategy 
{
	public $provider = null;
	protected $users = null;

	public function __construct($provider)
	{
		parent::__construct($provider);

		$this->provider = Auth_Provider_Normal::make();

		return $this;
	}
	
	public function authenticate()
	{
		// get user data from cookie
		$users = \Cookie::get('_users');

		// user data shouldn't be null if there user authentication available, if not populate from default
		if (null !== $users) 
		{
			$users = unserialize(\Crypt::decode($users));
		}
		else
		{
			$users        = new \stdClass();
			$users->id    = 0;
			$users->_hash = '';
		}

		$this->users = $users;

		$this->provider->access_token((array) $users);

		return $this;
	}

	public function reauthenticate()
	{
		// get user data from cookie
		$users = $this->users;

		// user data shouldn't be null if there user authentication available, if not populate from default
		if (null === $users) 
		{
			$users        = new \stdClass();
			$users->id    = 0;
		}

		$users->_hash = null;

		$this->users = $users;

		$this->provider->access_token((array) $users);

		return $this;
	}

}