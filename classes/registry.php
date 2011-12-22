<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.1
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
 * Registry Class
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Registry
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Registry 
{
	protected $instances = array();

	public static function forge($name = null)
	{
		return static::make($name);	
	}

	public static function make($name = null)
	{
		
	}

	protected $data = array();

	protected function __construct()
	{
		
	}

	public function get($key = null)
	{
		
	}

	public function __get($key)
	{
		
	}

	public function __set($key, $value)
	{
		
	}


}