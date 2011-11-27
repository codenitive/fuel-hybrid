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

class Template 
{
	/**
	 * Default template driver
	 *
	 * @var     constant
	 */
	const DEFAULT_TEMPLATE = 'normal';

	/**
	 * Cache template instance so we can reuse it on multiple request eventhough 
	 * it's almost impossible to happen
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $instances = array();

	/**
	 * Only load the configuration once
	 *
	 * @static
	 * @access  public
	 */
	public static function _init()
	{
		\Config::load('hybrid', 'hybrid');
	}

	/**
	 * Initiate a new Template instance
	 * 
	 * @static
	 * @access  public
	 * @return  Template_Abstract
	 */
	public static function forge($name = null)
	{
		if (null === $name)
		{
			$name = \Config::get('hybrid.template.default', self::DEFAULT_TEMPLATE);   
		}

		$name       = strtolower($name);

		$folder     = null;
		$filename   = null;
		$type       = explode('.', strval($name));


		if (count($type) > 1) 
		{
			// set filename if available
			if (isset($type[2]))
			{
				$filename = $type[2];
			}

			// folder should be available if type count > 1
			$folder = $type[1];
		}
		
		$type   = $type[0];
		$name   = $type.'.'.$folder;

		if ( ! isset(static::$instances[$name]))
		{
			$driver = "\Hybrid\Template_".ucfirst($type);
		 
			if (class_exists($driver)) 
			{
				// load a new template if class exist
				static::$instances[$name] = new $driver($folder, $filename);
			}
			else 
			{
				throw new \FuelException("Requested {$driver} does not exist.");
			}
		}
		
		return static::$instances[$name];
	}

	/**
	 * Initiate a new Template instance
	 * 
	 * @static
	 * @access  public
	 * @return  Template_Abstract
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