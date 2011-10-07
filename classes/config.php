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
	);
	
	
	public static function _init()
	{
		$available_drivers = array();
		foreach(static::$drivers as $name => $driver)
		{
			static::$drivers[$name] = new $driver;
		}
	}
	
	
	public static function load($file, $group = null, $reload = false, $overwrite = false)
	{
		if ( ! is_array($file) && array_key_exists($file, static::$loaded_files) and ! $reload)
		{
			return false;
		}
		
		$config = array();
		$ext = strtolower(substr($file, 0, 3));
		
		
		// check if is a direct include file
		if (is_array($file))
		{
			$config = $file;
		}
		elseif(is_string($file) and in_array($ext, array_keys(static::$drivers)) and strpos($file, $ext.'::'))
		{
			$ext = substr(strtolower($file), 0, 3);
			$file = str_replace($ext.'::', "", $file);
			if($paths = \Fuel::find_file('config', $file, '.'.$ext, true))
			{
				// Reverse the file list so that we load the core configs first and
				// the app can override anything.
				$paths = array_reverse($paths);
				

				foreach ($paths as $path)
				{
					$filepath = $paths[0];
					$ext = substr(strrchr($filepath,'.'),1);
					$config = $overwrite ? array_merge($config, static::$drivers[$ext]->load($filepath)) : \Arr::merge($config, static::$drivers[$ext]->load($filepath));
				}
			}
		}
		else 
		{
			$file = str_replace($ext.'::', '', $file);
			$paths = array();
			foreach(static::$drivers as $ext => $driver)
			{
				$paths = array_merge($paths, array_reverse(\Fuel::find_file('config', $file, '.'.$ext, true)));
			}
			

			foreach ($paths as $path)
			{
				$filepath = $paths[0];
				$ext = substr(strrchr($filepath,'.'),1);
				$config = $overwrite ? array_merge($config, static::$drivers[$ext]->load($filepath)) : \Arr::merge($config, static::$drivers[$ext]->load($filepath));
			}			
		}
		
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
	
	public static function save($file, $config)
	{
		if ( ! is_array($config))
		{
			if ( ! isset(static::$items[$config]))
			{
				return false;
			}
			$config = static::$items[$config];
		}
		
		$ext = strtolower(substr($file, 0, 3));			
		if(in_array($ext, array_keys(static::$drivers)) and strpos($file, $ext.'::'))
		{			
			$content = static::$drivers[$ext]->save($config);	
		}
		else 
		{
			$file = str_replace($ext.'::', '', $file);
			$ext = 'php';
			$content = static::$drivers[$ext]->save($config);	
		}
		
		if ( ! $path = \Fuel::find_file('config', $file, '.'.$ext))
		{
			if ($pos = strripos($file, '::'))
			{
				// get the namespace path
				if ($path = \Autoloader::namespace_path('\\'.ucfirst(substr($file, 0, $pos))))
				{
					// strip the namespace from the filename
					$file = substr($file, $pos+2);

					// strip the classes directory as we need the module root
					// and construct the filename
					$path = substr($path,0, -8).'config'.DS.$file.'.'.$ext;

				}
				else
				{
					// invalid namespace requested
					return false;
				}
			}

		}
		
		// make sure we have a fallback
		$path or $path = APPPATH.'config'.DS.$file.'.'.$ext;

		$path = pathinfo($path);

		return \File::update($path['dirname'], $path['basename'], $content);
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
	
}