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

class Template {

	protected static $_config = null;
	
	public static function _init()
	{
		if (is_null(static::$_config))
		{
			\Config::load('app', 'app');
			static::$_config = \Config::get('app.template', array());
		}
	}

	public static function factory($type = null)
	{
		return new static($type);
	}

	protected $_folder = 'default';
	protected $_filename = 'index';
	public $view = null;

	public function __construct($type = null)
	{
		$available_folders = array_keys(static::$_config['folders']);

		if (empty($available_folders))
		{
			throw new \Fuel_Exception("\\Hybrid\\Template configuration is empty");
		}

		if (in_array(trim(strval($type)), $available_folders))
		{
			$this->_folder = static::$_config['folders'][$type];
		}

		$this->_filename = static::$_config['default_filename'];

		if (!!static::$_config['load_assets'])
		{
			$this->load_assets();
		}

		$this->view = \Hybrid\View::factory();
	}

	public function load_assets() {
		if (!\is_dir($this->_folder . 'assets/'))
		{
			throw new \Fuel_Exception('Unable to load assets');
		}
		else
		{
			\Assets::add_path($this->_folder . 'assets/');
		}

		return $this;
	}

	public function set_folder($path = null)
	{
		if (!\is_dir($path))
		{
			throw new \Fuel_Exception('Not a valid folder');
		}
		else 
		{
			$this->_folder = $path;

			if (!!static::$_config['load_assets'])
			{
				return $this->load_assets();
			}
		}

		return $this;
	}

	public function set_filename($filename = null)
	{
		if (!empty($filename))
		{
			$this->_filename = $filename;
		}

		return $this;
	}

	public function set($data = array())
	{
		if (is_array($data) and count($data) > 0)
		{
			foreach ($data as $key => $value)
			{
				$this->view->set($key, $value);
			}
		}

		return $this;
	}

	public function render()
	{
		$this->view->set_path($this->_folder);
		$this->view->set_filename($this->_filename);
		$this->view->auto_encode(static::$_config['auto_encode']);

		return $this->view->render();
	}

}