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

Factory::import('decoda/decoda', 'vendor');

use \Decoda;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Parser_BBCode
 * @author      Ignacio Muñoz Fernandez <nmunozfernandez@gmail.com>
 */

class Parser_Bbcode extends Parser_Driver 
{
	/**
	 * Construct a new instance (don't do anything right now)
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct() {}
	
	/**
	 * Parse bbcode formatted text to HTML
	 *
	 * @access  public
	 */
	public function parse($text = '')
	{
		if (empty($text) or ! strval($text))
		{
			$text = '';
		}

		$decoda = new Decoda($text);
		return $decoda->parse(true);
	}

}