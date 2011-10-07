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
 * @category    Config
 * @author      Ignacio "kavinsky" Muñoz Fernandez <nmunozfernandez@gmail.com>
 */
class Config extends \Fuel\Core\Config
{
	/**
	 * List of all available driver classes
	 * 
	 * @var array
	 */
	static public $drivers = array(
			'php' => '\\Hybrid\\Config_Php',
			'yml' => '\\Hybrid\\Config_Yml',
			'xml' => '\\Hybrid\\Config_Xml',
			'ini' => '\\Hybrid\\Config_Ini',
			'db'  => '\\Hybrid\\Config_Db',
			'redis' => '\\Hybrid\\Config_Redis',
			'mongo' => '\\Hybrid\\Config_Mongo',
	);
	
	
	public static function _init()
	{
		$available_drivers = array();
		foreach(static::$drivers as $name => $driver)
		{
			static::$drivers[$name] = new $driver;
		}
		
		\Debug::dump(static::$drivers);
	}
	
	
	public static function load($file, $group = null, $reload = false, $overwrite = false)
	{
		if ( ! is_array($file) && array_key_exists($file, static::$loaded_files) and ! $reload)
		{
			return false;
		}
		
		$config = array();
		// check if is a direct include file
		if (is_array($file))
		{
			$config = $file;
		}
		elseif(is_string($file) and in_array(strtolower(substr($file, 0, 3)), array_keys(static::$drivers)))
		{
			$ext = substr(strtolower($file), 0, 3);
			var_dump($ext);
			$file = str_replace($ext.'::', "", $file);
			if($paths = \Fuel::find_file('config', $file, '.'.$ext, true))
			{
				// Reverse the file list so that we load the core configs first and
				// the app can override anything.
				$paths = array_reverse($paths);
				
				var_dump(file_get_contents($paths[0]));
				exit;
				foreach ($paths as $path)
				{
					$config = $overwrite ? array_merge($config, static::$drivers[$ext]->load($path)) : \Arr::merge($config, static::$drivers[$path]->load($file));
				}
			}
		}
		else 
		{
			$paths = array();
			foreach(static::$drivers as $ext => $driver)
			{
				$paths = array_merge($paths, array_reverse(\Fuel::find_file('config', $file, '.'.$ext, true)));
			}
			
			if(count($paths) > 0)
			{
				$filepath = $paths[0];
				$ext = substr(strrchr($filepath,'.'),1);
				$config = $overwrite ? array_merge($config, static::$drivers[$ext]->load($filepath)) : \Arr::merge($config, static::$drivers[$ext]->load($filepath));
			}
			
			
		}
		\Debug::dump($config);
		if ($group === null)
		{
			static::$items = $reload ? $config : ($overwrite ? array_merge(static::$items, $config) : \Arr::merge(static::$items, $config));
		}
		else
		{
			$group = ($group === true) ? $file : $group;
			if ( ! isset(static::$items[$group]) or $reload)
			{
				static::$items[$group] = array();
			}
			static::$items[$group] = $overwrite ? array_merge(static::$items[$group],$config) : \Arr::merge(static::$items[$group],$config);
		}

		if ( ! is_array($file))
		{
			static::$loaded_files[$file] = true;
		}
		return $config;
		
	}
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
			$driver_class = 'config_'.$drivername;
		}
		
		if(!class_exists($driver_class) and get_parent_class($driver_class) === "Config_Driver")
		{
			throw new Fuel_Exception("Config driver class $driver_class not found");
			return false;
		}
		
		$driver_class = Inflector::classify($driver_class);
		
		static::$drivers[$driver_name] = new $driver_class();
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
			unset(static::$drivers[$driver_name]);
			return true;
		}
		
		throw new Fuel_Exception("Config driver $driver_name are not registered");
		return false;
	}
	
	public static function available_drivers()
	{
		return static::$drivers;
	}
	
	protected static function check_extension($string)
	{
		
		$string = substr($string, 0, 3);
		
	}
}