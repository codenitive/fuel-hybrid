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

	public static function factory($type = null)
	{
		return new static($type, $config);
	}

	public function __construct($type = null)
	{
		$driver = '\\Hybrid\\Template_'.ucfirst(strval($type));

		if (class_exists($driver)) 
		{
			return new $driver($config);
		}
		else 
		{
			throw new \Fuel_Exception("Requested {$driver} does not exist");
		}
	}

}