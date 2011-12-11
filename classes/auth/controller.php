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
 * @category    Auth_Controller
 */

 /**
 * Auth Controller Class taken from NinjAuth Package for FuelPHP
 *
 * @package     NinjAuth
 * @author      Phil Sturgeon <https://github.com/philsturgeon>
 */

class Auth_Controller extends \Controller 
{
	/**
	 * Load autho configuration
	 *
	 * @access  public
	 * @return  void
	 */
	public function before()
	{
		parent::before();

		// Load the configuration for this provider
		\Config::load('autho', 'autho');
	}

	/**
	 * Start a session request
	 *
	 * @access  public
	 * @param   array    $provider
	 * @return  Response
	 * @throws  Auth_Strategy_Exception
	 */
	public function action_session($provider = array())
	{
		if (empty($provider))
		{
			throw new \HttpNotFoundException();
		}

		try 
		{
			Auth_Strategy::make($provider)->authenticate();
		}
		catch (Auth_Strategy_Exception $e)
		{
			return $this->action_error($provider, $e->getMessage());
		}
	}

	/**
	 * Get authorization code from callback and fetch user access_token and other information
	 *
	 * @access  public
	 * @param   array    $provider
	 * @return  Response
	 * @throws  Auth_Strategy_Exception
	 */
	public function action_callback($provider = array())
	{
		if (empty($provider))
		{
			throw new \HttpNotFoundException();
		}
		
		try 
		{
			$strategy = Auth_Strategy::make($provider);
			Auth_Strategy::login_or_register($strategy);
		} 
		catch (Auth_Strategy_Exception $e)
		{
			return $this->action_error($provider, $e->getMessage());
		}
	}

	/**
	 * Display error from failed request
	 *
	 * @access  protected
	 * @param   array    $provider
	 * @param   string   $e
	 * @return  Response
	 */
	protected function action_error($provider = array(), $e = '')
	{
		return \View::forge('error');
	}

}