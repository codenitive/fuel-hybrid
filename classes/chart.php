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

use \FuelException;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * Google APIs Visualization Library Class
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Chart
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Chart 
{
	/**
	 * Cache Chart instance so we can reuse it on multiple request.
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $instances = array();

	/**
	 * Initiate a new Chart_Driver instance.
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  Chart_Driver 
	 * @throws  \FuelException
	 */
	public static function __callStatic($method, array $arguments) 
	{
		if ( ! in_array($method, array('factory', 'forge', 'instance', 'make')))
		{
			throw new FuelException(__CLASS__.'::'.$method.'() does not exist.');
		}

		$name = empty($arguments) ? null : $arguments[0];
		$name = $name ?: 'default';
		$name = strtolower($name);

		if ( ! isset(static::$instances[$name]))
		{
			$driver = "\Hybrid\Chart_".ucfirst($name);
			
			if (class_exists($driver))
			{
				static::$instances[$name] = new $driver();
			}
			else 
			{
				throw new FuelException("Requested {$driver} does not exist.");
			}
		}

		return static::$instances[$name];
	}

	/**
	 * Hybrid\Chart doesn't support a construct method
	 *
	 * @access  protected
	 */
	protected function __construct() {}
	
	/**
	 * Load Google JavaSript API Library
	 *
	 * @static
	 * @access  public
	 * @return  string
	 */
	public static function js() 
	{
		return '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
	}

}