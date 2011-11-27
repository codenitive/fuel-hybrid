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
	 * @return  static 
	 */
	public static function forge($name = null) 
	{
		if (is_null($name))
		{
			$name = 'default';
		}

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
				throw new \FuelException("Requested {$driver} does not exist.");
			}
		}

		return static::$instances[$name];
	}

	/**
	 * Initiate a new Chart_Driver instance.
	 * 
	 * @static
	 * @access  public
	 * @return  static 
	 */
	public static function make($name = null) 
	{
		return static::forge($name);
	}

	/**
	 * Shortcode to self::forge().
	 *
	 * @deprecated  1.3.0
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  self::forge()
	 */
	public static function factory($name = null)
	{
		\Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
		
		return static::forge($name);
	}

	/**
	 * Get cached instance, or generate new if currently not available.
	 *
	 * @static
	 * @access  public
	 * @return  Chart_Driver
	 * @see     self::forge()
	 */
	public static function instance($name = null)
	{
		return static::forge($name);
	}
	
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