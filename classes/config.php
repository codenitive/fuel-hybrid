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

use Fuel\Core\Inflector,
	Fuel\Core\Fuel_Exception;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Config
 * @author      Ignacio "kavinsky" Muñoz Fernandez <nmunozfernandez@gmail.com>
 */
class Config extends Fuel\Core\Config
{
	/**
	 * List of all available driver classes
	 * 
	 * @var array
	 */
	static protected $available_drivers = array(
			'php' => 'Config_Php',
			'yml' => 'Config_Yml',
			'xml' => 'Config_Xml',
			'ini' => 'Config_Ini',
			'db'  => 'Config_Db',
			'redis' => 'Config_Redis',
			'mongo' => 'Config_Mongo',
	);
	
	/**
	 * Used to register a new config driver class
	 * 
	 * @param string $driver_name	alias for the driver, used to be loaded as extension or symlink
	 * @param string $driver_class 	Name of the driver class
	 * @access public
	 * @static
	 * @return boolean
	 * @throws Fuel_Exception
	 */
	public static function register_driver($driver_name, $driver_class = null)
	{
		$driver_name = strtolower($driver_name);
		$driver_class = strtolower($driver_class);
		if(!$driver_class === "")
		{
			$driver_class = 'model_'.$drivername;
		}
		
		if(!class_exists($driver_class))
		{
			throw new Fuel_Exception("Config driver class $driver_class not found");
			return false;
		}
		
		$driver_class = Inflector::classify($driver_class);
		
		static::$available_drivers[$driver_name] = $driver_class;
		return true;
	}
	
	/**
	 * Used to unregister a config driver
	 * 
	 * @param string $driver_name	Alias of the driver to unregister
	 * @access public
	 * @static
	 * @return boolean
	 * @throws Fuel_Exception
	 */
	public static function unregister_driver($driver_name)
	{
		if(array_key_exists($driver_name))
		{
			unset(static::$available_drivers[$driver_name]);
			return true;
		}
		
		throw new Fuel_Exception("Config driver $driver_name are not registered");
		return false;
	}
	
	public static function available_drivers()
	{
		return static::$available_drivers;
	}
}
