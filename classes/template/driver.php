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
 * @category    Template_Driver
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Template_Driver 
{
	/**
	 * Template driver configuration
	 *
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $config = null;
	
	/**
	 * Load configurations
	 *
	 * @static
	 * @access  public
	 * @return  void
	 */
	public static function _init()
	{
		if (null === static::$config)
		{
			\Config::load('hybrid', 'hybrid');
			static::$config = \Config::get('hybrid.template', array());
		}
	}

	/**
	 * Folder location
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $folder = 'default';

	/**
	 * Filename
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $filename = 'index';

	/**
	 * Adapter \Fuel\Core\View
	 *
	 * @access  public
	 * @var     object
	 */
	public $view = null;

	 /**
	 * List of loaded asset
	 *
	 * @access  protected
	 * @staticvar   array
	 */
	protected static $assets = array();

	/**
	 * Load asset as subfolder of template
	 *
	 * @access  public
	 * @param   bool    $forced_load
	 * @return  self
	 * @throws  \FuelException
	 */
	public function load_assets($forced_load = false) 
	{
		$folder_path = $this->folder.'assets/';

		if (false === static::$config['load_assets'] and false === $forced_load)
		{
			return $this;
		}

		if ( ! is_dir($folder_path))
		{
			throw new \FuelException(__METHOD__.": Unable to load assets at {$folder_path}.");
		}
		else
		{
			$folder_path = str_replace(DOCROOT, '', $folder_path);

			if ( ! in_array($folder_path, static::$assets))
			{
				\Asset::add_path($folder_path);
				array_push(static::$assets, $folder_path);
			}
		}

		return $this;
	}

	/**
	 * Set folder location
	 *
	 * @access  public
	 * @param   string  $path
	 * @return  self
	 * @throws  \FuelException
	 */
	public function set_folder($path = null)
	{
		if ( ! is_dir($path))
		{
			throw new \FuelException(__METHOD__.": Path {$path} does not appear to a valid folder.");
		}
		else 
		{
			$this->folder = $path;
		}

		return $this;
	}

	/**
	 * Set filename location
	 *
	 * @access  public
	 * @param   string  $filename
	 * @return  self
	 */
	public function set_filename($filename = null)
	{
		if ( ! empty($filename))
		{
			$this->filename = $filename;
		}

		return $this;
	}

	/**
	 * Set data
	 *
	 * @access  public
	 * @param   array   $data
	 * @return  self
	 */
	public function set($data = array())
	{
		if (is_array($data) and count($data) > 0)
		{
			$this->view->set($data);
		}

		return $this;
	}

	/**
	 * Load partial view
	 *
	 * @abstract
	 * @access  public
	 * @param   string  $filename
	 * @param   array   $data
	 * @return  void
	 */
	public abstract function partial($filename, $data = null);

	/**
	 * Render template
	 *
	 * @abstract
	 * @access  public
	 */
	public abstract function render();

	/**
	 * Shortcut to render()
	 *
	 * @access  public
	 */
	public function __toString()
	{
		return $this->render();
	}

}