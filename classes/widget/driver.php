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

use \Config;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Widget_Driver
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Widget_Driver 
{
	/**
	 * List of items
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $items = array();
	
	/**
	 * Name of this instance
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $name = null;
	
	/**
	 * Configuration
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $config = array();

	/**
	 * Type
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $type = null;

	/**
	 * Construct a new instance
	 *
	 * @access  public
	 * @param   string  $name
	 * @param   array   $config
	 * @return  void
	 */
	public function __construct($name, $config)
	{
		$this->config = Config::get('hybrid.widget.'.$this->type, array());
		$this->name   = $name;
		$this->config = array_merge($config, $this->config);
	}

	/**
	 * Append a new item
	 *
	 * @access  public
	 * @param   string  $title
	 * @param   string  $content
	 * @return  self
	 * @see     self::add()
	 */
	public function append($title, $content = '')
	{
		return $this->add($title, $content, false);
	}

	/**
	 * Prepend a new item
	 *
	 * @access  public
	 * @param   string  $title
	 * @param   string  $content
	 * @return  self
	 * @see     self::add()
	 */
	public function prepend($title, $content = '')
	{
		return $this->add($title, $content, true);
	}

	

	/**
	 * Shortcut to render()
	 *
	 * @access  public
	 * @see     self::render()
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Add a new item, prepending or appending
	 *
	 * @access  public
	 * @param   string  $title
	 * @param   string  $content
	 * @param   bool    $prepend
	 * @return  self
	 */
	public abstract function add($title, $content = '', $prepend = false);

	/**
	 * Render Tab as a view
	 *
	 * @access  public
	 * @return  string
	 */
	public abstract function render();

}