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
 * @category    Chart
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Chart extends \Fuel\Core\TestCase 
{    
	/**
	 * Setup the test
	 */
	public function setup()
	{
		\Package::load('hybrid');
	}

	/**
	 * Test Chart::forge()
	 *
	 * @test
	 */
	public function test_forge()
	{
		$output = Chart::forge('timeline');

		$this->assertTrue($output instanceof \Hybrid\Chart_Timeline);
	}

	/**
	 * Test Chart::forge() given invalid driver
	 *
	 * @test
	 * @expectedException \FuelException
	 */
	public function test_forge_expected_exception_given_invalid_driver()
	{
		$output = Chart::forge('date');
	}

	/**
	 * Test Chart::js()
	 *
	 * @test
	 */
	public function test_js()
	{
		$expected = '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
		$output   = Chart::js();

		$this->assertEquals($expected, $output);
	}

 }