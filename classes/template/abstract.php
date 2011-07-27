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
 * @category    Template_Abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Template_Abstract {

	protected static $config = null;
	
	public static function _init()
	{
		if (is_null(static::$config))
		{
			\Config::load('app', 'app');
			static::$config = \Config::get('app.template', array());
		}
	}

	public static function factory()
	{
		return new static();
	}

	protected $folder = 'default';
	protected $filename = 'index';
	public $view = null;

	public abstract function __construct ();

	public function load_assets() {
		$folder_path = $this->folder . 'assets/';

		if (!\is_dir($folder_path))
		{
			throw new \Fuel_Exception("Unable to load assets at {$folder_path}");
		}
		else
		{
			\Assets::add_path($folder_path);
		}

		return $this;
	}

	public function set_folder($path = null)
	{
		if (!\is_dir($path))
		{
			throw new \Fuel_Exception("Path {$path} does not appear to a valid folder");
		}
		else 
		{
			$this->folder = $path;

			if (!!static::$config['load_assets'])
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
			$this->filename = $filename;
		}

		return $this;
	}

	public function set($data = array())
	{
		if (is_array($data) and count($data) > 0)
		{
			$this->view->set($data);
		}

		return $this;
	}

	public abstract function partial($filename, $data = null);

	public abstract function render();

}