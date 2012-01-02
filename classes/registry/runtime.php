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
 * @category    Registry_Runtime
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Registry_Runtime extends Registry_Driver
{
	/**
	 * @access  protected
	 * @var     string  storage configuration, currently only support runtime.
	 */
	protected $storage = 'runtime';

	public function initiate() {}

	public function shutdown() {}
	
}