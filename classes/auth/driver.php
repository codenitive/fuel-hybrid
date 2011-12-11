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
 * @category    Auth_Driver
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Auth_Driver 
{
	/**
	 * Adapter object
	 *
	 * @access  protected
	 * @var     object
	 */
	protected $strategy  = null;
	
	/**
	 * Auth data
	 *
	 * @static
	 * @access  protected
	 * @var     object|array
	 */
	protected $data     = null;

	/**
	 * Return TRUE/FALSE whether visitor is logged in to the system
	 * 
	 * Usage:
	 * 
	 * <code>false === \Hybrid\Auth::make()->is_logged()</code>
	 *
	 * @access  public
	 * @return  bool
	 */
	public function is_logged()
	{
		return ($this->data['id'] > 0 ? true : false);
	}

	/**
	 * Get current user authentication
	 * 
	 * Usage:
	 * 
	 * <code>$user = \Hybrid\Auth::make()->get();</code>
	 *
	 * @access  public
	 * @param   string  $name optional key value, return all if $name is null
	 * @return  object
	 */
	public function get($name = null)
	{
		if (null === $name)
		{
			return (object) $this->data;
		}
		elseif (array_key_exists($name, $this->data))
		{
			return $this->data[$name];
		}

		return null;
	}

}