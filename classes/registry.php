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

	protected static function _init()
	{
		\Config::load('hybrid', 'hybrid');
	}

	/**
	 * Shortcode to self::make().
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name       instance name
	 * @return  self::make()
	 */
	public static function forge($name = null)
	{
		return static::make($name);	
	}

	/**
	 * Initiate a new Registry instance
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name       instance name
	 * @return  object
	 */
	public static function make($name = null)
	{
		if (null === $name)
		{
			$name = 'default';
		}

		if ( ! isset(static::$instances[$name]))
		{
			static::$instances[$name] = new static();
		}

		return static::$instances[$name];
	}

	/**
	 * @access  protected
	 * @var     array   collection of key-value pair of either configuration or data
	 */
	protected $data = array();

	/**
	 * @access  protected
	 * @var     string  storage configuration, currently only support runtime.
	 */
	protected $storage = 'runtime';

	/**
	 * Construct an instance.
	 *
	 * @access  protected
	 * @param   string  $storage    set storage configuration (default to 'runtime').
	 */
	protected function __construct($storage = 'runtime') 
	{
		$this->storage = $storage;
	}

	/**
	 * Get value of a key
	 *
	 * @access  public
	 * @param   string  $key        A string of key to search.
	 * @param   mixed   $default    Default value if key doesn't exist.
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		return \Arr::get($this->data, $key, $default);
	}

	/**
	 * Set a value from a key
	 *
	 * @access  public
	 * @param   string  $key        A string of key to add the value.
	 * @param   mixed   $value      The value.
	 * @return  void
	 */
	public function set($key, $value = '')
	{
		\Arr::set($this->data, $key, $value);
	}

	/**
	 * Delete value of a key
	 *
	 * @access  public
	 * @param   string  $key        A string of key to delete.
	 * @return  bool
	 */
	public function delete($key)
	{
		return \Arr::delete($this->data, $key);
	}

}