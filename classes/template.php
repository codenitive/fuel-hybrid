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
 * @category    Template
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Template {

	protected static $instances = array();

	public static function factory($type = null)
	{
		$theme = null;
		$type = explode('.', strval($type));

		if (count($type) > 1) 
		{
			$theme = $type[1];
		}
		
		$type = $type[0];

		$driver = '\\Hybrid\\Template_'.ucfirst($type);

		if (isset(static::$instances[$type]))
		{
			return static::$instances[$type];
		}
		elseif (class_exists($driver)) 
		{
			static::$instances[$type] = new $driver($theme);
			return static::$instances[$type];
		}
		else 
		{
			throw new \Fuel_Exception("Requested {$driver} does not exist");
		}
	}

}