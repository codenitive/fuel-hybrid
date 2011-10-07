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
 * @category    Config
 * @author      Ignacio "kavinsky" Muñoz Fernandez <nmunozfernandez@gmail.com>
 */
class Config_Php extends Config_Driver
{
	/**
	 * File extensions this is only applied to file system config type
	 * 
	 * @access protected
	 * @var string
	 */
	protected $extension = 'php';
	
	/**
	 * Load prefix to specify from where it loads the config file in first instance
	 * 
	 * @access protected
	 * @var string
	 */
	protected $load_prefix	= 'php';
	
	
	public function load($file)
	{
		
		var_dump($file);
		
		$configs = array();
		
		// always return these two values	
		return array('file' => $filename, 'configs' => $configs);
	}
	
	/**
	 * 
	 */
	public function save()
	{
		
	}
}
