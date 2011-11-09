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
 * @category    Cli
 * @author      Ignacio Muñoz Fernandez <nmunozfernandez@gmail.com>
 */

class Cli extends \Fuel\Core\Cli 
{
	/**
	 * An alias for Cli::write() to output $text only when specify -v or --verbose options
	 * 
	 * @access  public
	 * @param   string|array    $text   the text to output, or array of lines
	 */
	public static function verbose($text = '', $foreground = null, $background = null)
	{
		if (static::option('v') !== null or static::option('verbose') !== null)
		{
			static::write($text, $foreground, $background);
		}
	}
}
