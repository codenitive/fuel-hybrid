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
 * @deprecated
 * @category    Controller_Frontend
 * @abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Controller_Frontend extends Controller_Template 
{
	/**
	 * Page template
	 * 
	 * @access  public
	 * @var     string
	 */
	public $template = 'frontend';
	
	/**
	 * Auto render template
	 * 
	 * @access  public
	 * @var     bool    
	 */
	public $auto_render = true;

	/**
	 * This method will be called after we route to the destinated method
	 * 
	 * @access  public
	 * @return  void
	 */
	public function before() 
	{
		\Log::warning('This Class is deprecated. Please use a Hybrid\Controller_Template instead.', __CLASS__);
		
		return parent::before();
	}

}