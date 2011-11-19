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
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Input
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Input 
{
	/**
	 * Store \Hybrid\Request object (if available)
	 * 
	 * @access      protected
	 * @staticvar   mixed 
	 */
	protected static $request = null;

	/**
	 * Receive \Hybrid\Request connection information
	 * 
	 * @static
	 * @access  public
	 * @param   string  $method
	 * @param   array   $data
	 */
	public static function connect($method = '', $data = array()) 
	{
		if ( ! empty($method)) 
		{
			static::$request = (object) array('method' => $method, 'data' => $data);
		}
	}

	/**
	 * Disconnect current \Hybrid\Request connection
	 * 
	 * @static
	 * @access  public
	 */
	public static function disconnect() 
	{
		static::$request = null;
	}

	/**
	 * Magic method to handle every static method available in \Fuel\Core\Input
	 *
	 * @static
	 * @access  public
	 * @return  mixed
	 */
	public static function __callStatic($name, $args) 
	{
		// If $request is null, it's a request from \Fuel\Core\Request so use it instead
		if (in_array(strtolower($name), array('is_ajax', 'protocol', 'real_ip', 'referrer', 'server', 'uri', 'user_agent'))) 
		{
			return call_user_func_array(array("Fuel\Core\Input", $name), $args);
		}
		
		// Check whether this request is from \Fuel\Core\Request or \Hybrid\Request
		$using_hybrid = false;
		
		$default      = null;
		$index        = null;
		
		if (null !== static::$request and '' !== static::$request->method) 
		{
			$using_hybrid = true;
		}

		if ( ! $using_hybrid and in_array($name, array('method', 'all'))) 
		{
			return call_user_func(array("Fuel\Core\Input", $name));
		}

		switch (true) 
		{
			case count($args) > 1 :
				$default = $args[1];
			case count($args) > 0 :
				$index   = $args[0];
			break;
		}

		switch ($name)
		{
			case 'method' :
				return static::$request->method;
			break;

			case 'all' :
				return static::$request->data;
			break;
		}

		// Reach this point but $index is null (which isn't be so we should just return the default value) 
		if (null === $index) 
		{
			return $default;
		}

		if ( ! $using_hybrid or $name === 'file') 
		{
			// Not using \Hybrid\Request, it has to be from \Fuel\Core\Input.
			return call_user_func_array(array("Fuel\Core\Input", $name), array($index, $default));
		}

		switch (true)
		{
			case strtoupper($name) === static::$request->method :
			case 'param' === $name :
			case 'get_post' === $name and in_array(static::$request->method, array('GET', 'POST')) :
				return isset(static::$request->data[$index]) ? static::$request->data[$index] : $default;
			break;

			default :
				return $default;
			break;
		}
	}

}