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
 * @category    Template_Normal
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Template_Normal extends Template_Abstract {

	public function __construct()
	{
		// Assets shouldn't be added in APPPATH/views at all
		
		$this->folder = static::$config['default_folder'];
		$this->filename = static::$config['default_filename'];

		if (!!static::$config['load_assets'])
		{
			$this->load_assets();
		}

		$this->view = \View::factory();
	}

	public function load_assets() {
		// Assets shouldn't be added in APPPATH/views at all

		return $this;
	}

	public function partial($filename, $data = null)
	{
		$view = \View::factory();
		$view->set_filename(rtrim($this->folder, '/') . '/' . $filename);
		$view->auto_encode(static::$config['auto_encode']);

		if (is_array($data) and count($data) > 0)
		{
			$view->set($data);
		}

		return $view->render();
	}

	public function render()
	{
		$this->view->set_filename(rtrim($this->folder, '/') . '/' . $this->filename);
		$this->view->auto_encode(static::$config['auto_encode']);

		return $this->view->render();
	}

}