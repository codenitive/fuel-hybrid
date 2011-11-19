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
 * @author      Phil Sturgeon <https://github.com/philsturgeon>
 */

abstract class Auth_Strategy 
{
	public $provider = null;
	public $config   = array();
	public $name     = null;
	
	/**
	 * List of available provider
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $providers = array(
		'normal'    => 'Normal',
		'facebook'  => 'OAuth2',
		'twitter'   => 'OAuth',
		'dropbox'   => 'OAuth',
		'flickr'    => 'OAuth',
		'google'    => 'OAuth2',
		'github'    => 'OAuth2',
		'linkedin'  => 'OAuth',
		'unmagnify' => 'OAuth2',
		'youtube'   => 'OAuth',
	);

	/**
	 * Generic construct method
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($provider)
	{
		$this->provider = $provider;
		
		$this->config   = \Config::get("autho.providers.{$provider}");
		
		if (null === $this->name)
		{
			// Attempt to guess the name from the class name
			$class_name = \Inflector::denamespace(get_class($this));
			$this->name = strtolower(str_replace('Auth_Strategy_', '', $class_name));
		}
	}

	/**
	 * Forge a new strategy
	 *
	 * @static
	 * @access  public
	 * @return  Auth_Strategy
	 * @throws  Auth_Strategy_Exception
	 */
	public static function forge($provider)
	{
		$strategy = \Config::get("autho.providers.{$provider}.strategy") ?: \Arr::get(static::$providers, $provider);
		
		if ( ! $strategy)
		{
			throw new Auth_Strategy_Exception(sprintf('Provider "%s" has no strategy.', $provider));
		}
		
		$class = "\Hybrid\Auth_Strategy_{$strategy}";
		return new $class($provider);
	}

	/**
	 * Deprecated factory method (adviced to use forge())
	 *
	 * @static
	 * @access  public
	 * @see     self::forge()
	 */
	public static function factory($provider)
	{
		\Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);

		return static::forge($provider);
	}

	/**
	 * Determine whether authenticated user should be continue to login or register new user
	 *
	 * @static
	 * @access 	public
	 * @param   object   $strategy
	 * @return  void
	 * @throws  Auth_Strategy_Exception
	 */
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
			if (0 === $num_linked or true === \Config::get('autho.link_multiple_providers'))
			{
				$user_hash = static::get_user_info($strategy, $response);

				try 
				{
					Auth::instance('user')->link_account($user_hash);
				}
				catch (AuthException $e)
				{
					throw new Auth_Strategy_Exception("Unable to retrieve valid user information from requested access token");
				}


				// Attachment went ok so we'll redirect
				Auth::redirect('logged_in');
			}
			else
			{
				$providers = array_keys($accounts);

				throw new Auth_Strategy_Exception(sprintf('This user is already linked to "%s".', $providers[0]));
			}
		}
		// The user exists, so send him on his merry way as a user
		else 
		{
			try 
			{
				$secret = '';
				if (null !== $response->secret)
				{
					$secret = $response->secret;
				}
				
				Auth::instance('user')->login_token($response->token, $response->secret);
				// credentials ok, go right in
				Auth::redirect('logged_in');
			}
			catch (AuthException $e)
			{
				$user_hash = static::get_user_info($strategy, $response);
				
				\Session::set('autho', $user_hash);

				Auth::redirect('registration');
			}
		}
	}

	/**
	 * Get user information from provider
	 *
	 * @static
	 * @access 	protected
	 * @param   object      $strategy
	 * @param   object      $response
	 * @return  array
	 * @throws  Auth_Strategy_Exception
	 */
	protected static function get_user_info($strategy, $response)
	{
		switch ($strategy->name)
		{
			case 'oauth':
				$user_hash = $strategy->provider->get_user_info($strategy->consumer, $response);
			break;

			case 'oauth2':
				$user_hash = $strategy->provider->get_user_info($response->token);
			break;

			case 'openid':
				$user_hash = $strategy->get_user_info($response);
			break;

			default:
				throw new Auth_Strategy_Exception('Unable to get user info with '.$strategy->name);
		}

		return $user_hash;
	}

	abstract public function authenticate();

}