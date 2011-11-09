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
 * @category    Factory
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Factory extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup() 
	{
		\Package::load('hybrid');
	}

	/**
	 * Test Factory::get_language();
	 *
	 * @test
	 */
	public function test_language() 
	{
		$expected = \Config::get('language');
		$output   = Factory::get_language();
		
		$this->assertEquals($expected, $output);
	}
	
	/**
	 * Test Factory::get_identity();
	 *
	 * @test
	 */
	public function test_identity() 
	{
		$expected = \Config::get('app.identity');
		$output = \Hybrid\Factory::get_identity();
		
		$this->assertEquals($expected, $output);
	}

}