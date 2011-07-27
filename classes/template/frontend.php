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
 * @category    Template_Frontend
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Template_Frontend extends Template_Abstract {

	public function __construct()
	{
		$available_folders = array_keys(static::$_config['folders']);

		if (empty($available_folders))
		{
			throw new \Fuel_Exception("\\Hybrid\\Template configuration is not completed");
		}

		if (in_array(trim(strval($type)), $available_folders))
		{
			$this->folder = static::$config['folders'][$type];
		}
		else
		{
			throw new \Fuel_Exception("Requested Template folder is not available");
		}

		$this->filename = static::$config['default_filename'];

		if (!!static::$config['load_assets'])
		{
			$this->load_assets();
		}

		$this->view = \Hybrid\View::factory();
	}

	public function partial($filename, $data = null)
	{
		$view = \Hybrid\View::factory();
		$view->set_path($this->folder);
		$view->set_filename($this->filename);
		$view->auto_encode(static::$config['auto_encode']);

		if (is_array($data) and count($data) > 0)
		{
			$view->set($data);
		}

		return $view->render();
	}

	public function render()
	{
		$this->view->set_path($this->folder);
		$this->view->set_filename($this->filename);
		$this->view->auto_encode(static::$config['auto_encode']);

		return $this->view->render();
	}

}