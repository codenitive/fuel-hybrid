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
	/**
	 * Cache registry instance so we can reuse it
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $instances = array();

	protected static $initiated = false;

	public static function _init()
	{
		if (true === static::$initiated)
		{
			return ;
		}

		\Config::load('hybrid', 'hybrid');
		\Event::register('shutdown', "\Hybrid\Registry::shutdown");

		static::$initiated = true;
	}

	/**
	 * Initiate a new Registry instance
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name       instance name
	 * @return  object
	 * @throws  \FuelException
	 */
	public static function __callStatic($method, array $arguments)
	{
		if ( ! in_array($method, array('factory', 'forge', 'instance', 'make')))
		{
			throw new \FuelException(__CLASS__.'::'.$method.'() does not exist.');
		}

		foreach (array(null, array()) as $key => $default)
		{
			isset($arguments[$key]) or $arguments[$key] = $default;
		}

		list($name, $config) = $arguments;

		list($instance_name, $config) = $arguments;
		
		$instance_name = $instance_name ?: 'runtime.default';
		$instance_name = strtolower($instance_name);

		if (false === strpos($instance_name, '.'))
		{
			$instance_name = $instance_name.'.default';
		}

		list($storage, $name) = explode('.', $instance_name, 2);

		switch ($storage)
		{
			case 'database' :
			case 'db' :
				$storage = 'database';
			break;
			case 'runtime' :
			default :
				$storage = 'runtime';
			break;
		}

		$instance_name = $storage.'.'.$name;
		
		if ( ! isset(static::$instances[$instance_name]))
		{
			$driver = "\Hybrid\Registry_".ucfirst($storage);

			// instance has yet to be initiated
			if (class_exists($driver))
			{
				static::$instances[$instance_name] = new $driver($name, $config);
			}
			else
			{
				throw new \FuelException("Requested {$driver} does not exist.");
			}
		}

		return static::$instances[$instance_name];
	}

	public static function shutdown()
	{
		foreach (static::$instances as $name => $class)
		{
			$class->shutdown();
		}
	}

}