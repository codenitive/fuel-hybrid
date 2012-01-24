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

use \FuelException;

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

class Template_Frontend extends Template_Driver 
{
	/**
	 * Initiate a new template using make
	 *
	 * Example:
	 * <code>$template = \Hybrid\Template_Frontend::make();</code>
	 *
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  Template_Frontend
	 * @throws  \FuelException
	 */
	public static function __callStatic($method, array $arguments)
	{
		if ( ! in_array($method, array('factory', 'forge', 'make')))
		{
			throw new FuelException(__CLASS__.'::'.$method.'() does not exist.');
		}

		$name   = empty($arguments) ? null : $arguments[0];

		$driver = 'frontend';
		$name   = strtolower($name);

		if ( ! empty($name))
		{
			$driver .= ".{$name}";
		}

		return Template::make($driver);
	}

	/**
	 * Initiate a new template object
	 *
	 * @access  public
	 * @param   string  $theme
	 * @param   string  $filename
	 * @return  void
	 */
	public function __construct($theme = null, $filename = null)
	{
		$this->set_theme($theme);

		if ( ! empty($filename) and '_default_' !== $filename)
		{
			$this->set_filename($filename);
		}
		else 
		{
			$this->set_filename(static::$config['default_filename']);
		}

		$this->view = View::forge();
	}

	/**
	 * Set theme location
	 *
	 * @access  public
	 * @return  self
	 * @throws  \FuelException
	 */
	public function set_theme($theme = null)
	{
		$available_folders = array_keys(static::$config['frontend']);

		if (empty($available_folders))
		{
			throw new FuelException(__METHOD__.": configuration is not completed");
		}

		if (null === $theme or '_default_' === $theme)
		{
			$theme = 'default';
		}

		if (in_array(trim(strval($theme)), $available_folders))
		{
			$this->set_folder(static::$config['frontend'][$theme]);
		}
		else
		{
			throw new FuelException(__METHOD__.": Requested {$theme} folder is not available.");
		}

		return $this;
	}

	/**
	 * Load partial view
	 *
	 * @access  public
	 * @param   string  $filename
	 * @param   array   $data
	 * @return  string
	 */
	public function partial($filename, $data = null)
	{
		$this->load_assets();
		
		$view = View::forge();
		$view->set_path($this->folder);
		$view->set_filename($filename);
		$view->auto_filter(static::$config['auto_filter']);

		if (is_array($data) and count($data) > 0)
		{
			$view->set($data);
		}

		$view->set('TEMPLATE_FOLDER', $this->folder, false);
		$view->set('template', $this, false);
		
		return $view;
	}

	/**
	 * Render self::view
	 *
	 * @access  public
	 * @return  string
	 */
	public function render()
	{
		$this->load_assets();

		$this->view->set_path($this->folder);
		$this->view->set_filename($this->filename);
		$this->view->auto_filter(static::$config['auto_filter']);

		$this->view->set('TEMPLATE_FOLDER', $this->folder, false);
		$this->view->set('template', $this, false);

		return $this->view;
	}

}