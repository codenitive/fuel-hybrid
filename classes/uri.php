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
 * @category    Uri
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Uri extends \Fuel\Core\Uri 
{
	/**
	 * Build query string
	 * 
	 * @static
	 * @access  public
	 * @param   mixed   $data
	 * @param   string  $start_with     Default string set to ?
	 * @return  string 
	 */
	public static function build_get_query($data, $start_with = '?') 
	{
		$values = array();

		if (is_string($data))
		{
			$data = array($data);
		}

		if (null === $data or ! is_array($data))
		{
			return '';
		}

		foreach ($data as $key => $value)
		{
			// Use $_GET value overwriting if given.
			if ( ! is_numeric($key))
			{
				$input = $value;
			}
			else
			{
				$key   = $value;
				$input = Input::get($value);
			}
			
			if (null !== $input and ! empty($input))
			{
				$values[$key] = $input;
			}
		}
		
		return $start_with.http_build_query($values);
	}
	
}