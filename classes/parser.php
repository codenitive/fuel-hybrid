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
 * @category    Parser
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Parser 
{
	/**
	 * Cache text instance so we can reuse it on multiple request eventhough 
	 * it's almost impossible to happen
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $instances = array();

	/**
	 * Initiate a new Parser instance
	 * 
	 * @static
	 * @access  public
	 * @return  object
	 */
	public static function forge($name = null)
	{
		if (null === $name)
		{
			$name = '';
		}

		$name = strtolower($name);

		if ( ! isset(static::$instances[$name]))
		{
			$driver = "\Hybrid\Parser_".ucfirst($name);
		
			// instance has yet to be initiated
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
	 * Initiate a new Parser instance
	 * 
	 * @static
	 * @access  public
	 * @return  object
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

}