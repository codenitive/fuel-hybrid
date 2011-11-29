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
 * @category    Auth_Driver_User
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Driver_User extends Auth_Driver 
{
	/**
	 * Adapter to \Hybrid\Acl
	 *
	 * @access  public
	 * @var     object
	 */
	public $acl         = null;
	
	protected $provider = null;

	/**
	 * Get Hybrid\Acl object, it's a quick way of get and use Hybrid\Acl without having to 
	 * initiate another call when this class already has it
	 * 
	 * Usage:
	 * 
	 * <code>$role = \Hybrid\Auth::make('user')->acl();
	 * $role->add_recources('monkeys');</code>
	 * 
	 * @access  public
	 * @param   string  $name
	 * @return  object
	 */
	public function acl($name = null) 
	{
		$this->acl = Acl::make($name);

		return $this->acl;
	}

	 /**
	 * Get self instance from cache instead of initiating a new object if time 
	 * we need to use this object
	 *
	 * @static
	 * @access  public
	 * @return  self
	 */
	public static function instance()
	{
		return Auth::make('user');
	}

	/**
	 * Load configurations
	 *
	 * @static 
	 * @access  public
	 * @return  void
	 */
	public static function _init()
	{
		\Config::load('autho', 'autho');
		\Config::load('crypt', true);
	}

	/**
	 * Initiate and check user authentication, the method will try to detect current 
	 * cookie for this session and verify the cookie with the database, it has to 
	 * be verify so that no one else could try to copy the same cookie configuration 
	 * and use it as their own.
	 * 
	 * @todo    need to use User-Agent as one of the hash value 
	 * 
	 * @access  private
	 * @return  bool
	 */
	public function __construct() 
	{
		// allow to disable user auth, would be useful when database not available
		if (false === \Config::get('autho.normal.enabled', true))
		{
			return;
		}

		$this->strategy = Auth_Strategy::make('normal')->authenticate();

		// short-hand variable
		$this->provider = $this->strategy->provider;
	}

	/**
	 * Return TRUE/FALSE whether visitor is logged in to the system
	 * 
	 * Usage:
	 * 
	 * <code>false === \Hybrid\Auth::make('user')->is_logged()</code>
	 *
	 * @access  public
	 * @return  bool
	 */
	public function is_logged()
	{
		return ($this->provider->data['id'] >= 1 ? true : false);
	}

	/**
	 * Get current user authentication
	 * 
	 * Usage:
	 * 
	 * <code>$user = \Hybrid\Auth::make('user')->get();</code>
	 *
	 * @access  public
	 * @param   string  $name optional key value, return all if $name is null
	 * @return  object
	 */
	public function get($name = null)
	{
		if (null === $name) 
		{
			return (object) $this->provider->data;
		}

		if (array_key_exists($name, $this->provider->data)) 
		{
			return $this->provider->data[$name];
		}

		return null;
	}

	/**
	 * Login user using normal authentication (username and password)
	 * 
	 * @access  public
	 * @param   string  $username
	 * @param   string  $password
	 * @param   bool    $remember_me
	 * @return  bool
	 */
	public function login($username, $password, $remember_me = false) 
	{
		$this->provider->login($username, $password, $remember_me);
		return true;
	}

	/**
	 * Login user using OAuth/OAuth2 token authentication (token and secret)
	 * 
	 * @access  public
	 * @param   array   $user_data
	 * @param   bool    $remember_me
	 * @return  bool
	 */
	public function login_token($user_data, $remember_me = false) 
	{
		$this->provider->login_token($user_data, $remember_me);
		return true;
	}

	/**
	 * Reauthenticate current user
	 * 
	 * @access  public
	 * @return  bool
	 */
	public function reauthenticate() 
	{
		$this->strategy->reauthenticate();
		return true;
	}

	/**
	 * Logout from user instance.
	 *
	 * Usage:
	 * 
	 * <code>\Hybrid\Auth::instance('user')->logout();</code>
	 * 
	 * @access  public
	 * @return  bool
	 */
	public function logout($redirect = true) 
	{
		$this->provider->logout();
		return true;
	}

	/**
	 * Link user account with external provider
	 *
	 * @access  public
	 * @param   array   $user_data
	 * @return  bool
	 */
	public function link_account($user_data)
	{
		if (true !== Auth::link_account($this->provider->data['id'], $user_data))
		{
			return false;
		}

		extract($user_data);

		$this->provider->data['accounts'][$provider] = array(
			'uid'           => $info['uid'],
			'access_token'  => isset($token->access_token) ? $token->access_token : '',
			'secret'        => isset($token->secret) ? $token->secret : '',
		);

		return true;
	}

}