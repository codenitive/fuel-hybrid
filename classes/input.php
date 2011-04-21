<?php

/**
 * Fuel
 *
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
class Input {

	protected static $request = null;

	public static function connect($method = '', $data = array()) 
	{
		if (!empty($method)) 
		{
			static::$request = (object) array('method' => $method, 'data' => $data);
		}
	}

	public static function disconnect() 
	{
		static::$request = null;
	}

	public static function __callStatic($name, $args) 
	{
		// If $request is null, it's a request from \Fuel\Core\Request so use it instead
		if (in_array(strtolower($name), array('is_ajax', 'user_agent', 'real_ip', 'referrer', 'server'))) 
		{
			return call_user_func(array('\\Input', $name));
		}
		
		// Check whether this request is from \Fuel\Core\Request or \Hybrid\Request
		$using_hybrid = false;
		
		if (!is_null(static::$request) && static::$request->method !== '') 
		{
			$using_hybrid = true;
		}

		if (!$using_hybrid && $name == 'method') 
		{
			return call_user_func(array('\\Input', 'method'));
		}

		$default = null;
		$index = null;

		switch (true) 
		{
			case count($args) > 1 :
				$default = $args[1];
			case count($args) > 0 :
				$index = $args[0];
				break;
		}

		if ($name === 'method') 
		{
			return static::$request->method;
		}

		// Reach this point but $index is null (which isn't be so we should just return the default value) 
		if (is_null($index)) 
		{
			return $default;
		}

		if (false === $using_hybrid) 
		{
			// Not using \Hybrid\Request, it has to be from \Fuel\Core\Input.
			return call_user_func_array(array('\\Input', $name), array($index, $default));
		}

		if ((strtoupper($name) === static::$request->method)) 
		{
			return isset(static::$request->data[$index]) ? static::$request->data[$index] : $default;
		} 
		else 
		{
			return $default;
		}
	}

}
