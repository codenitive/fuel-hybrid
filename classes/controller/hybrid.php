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
 * @category    Controller_Hybrid
 * @abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Controller_Hybrid extends Controller 
{
	
	/**
	 * Page template
	 * 
	 * @access  public
	 * @var     string
	 */
	public $template = 'normal';
	
	/**
	 * Auto render template
	 * 
	 * @access  public
	 * @var     bool    
	 */
	public $auto_render = true;
	
}