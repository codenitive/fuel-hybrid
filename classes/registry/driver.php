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
 * @category    Registry_Driver
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Registry_Driver
{
	protected $name = null;

	protected $config = array();

	/**
	 * @access  protected
	 * @var     array   collection of key-value pair of either configuration or data
	 */
	protected $data = array();

	/**
	 * @access  protected
	 * @var     string  storage configuration, currently only support runtime.
	 */
	protected $storage;

	/**
	 * Construct an instance.
	 *
	 * @access  public
	 * @param   string  $storage    set storage configuration (default to 'runtime').
	 */
	public function __construct($name = 'default', $config = array()) 
	{
		$this->name   = $name;
		$this->config = is_array($config) ? $config : array(); 

		$this->initiate();
	}

	/**
	 * Get value of a key
	 *
	 * @access  public
	 * @param   string  $key        A string of key to search.
	 * @param   mixed   $default    Default value if key doesn't exist.
	 * @return  mixed
	 */
	public function get($key = null, $default = null)
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
	public function delete($key = null)
	{
		return \Arr::delete($this->data, $key);
	}

	public abstract function initiate();
	public abstract function shutdown();

}