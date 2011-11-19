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
 * @author      Ignacio Muñoz Fernandez <nmunozfernandez@gmail.com>
 */

class Test_Currency extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup()
	{
		\Package::load('hybrid');
	}
	
	/**
	 * Test Currency::forge();
	 *
	 * @test
	 */
	public function test_forge()
	{
		$output = Currency::forge(1, 'USD');

		$this->assertTrue($output instanceof \Hybrid\Currency);
	}

	/**
	 * Test Currency::convert_to(); given same currency
	 *
	 * @test
	 */
	public function test_convert_to_given_same_currency()
	{
		$output = Currency::forge(1, 'USD')->convert_to('USD');
		$expected = (float) 1;

		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Currency::to_{currency}(); given same currency
	 *
	 * @test
	 */
	public function test_to_currency_given_same_currency()
	{
		$output = Currency::forge(1, 'USD')->to_usd();
		$expected = (float) 1;

		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Currency::fetch_currency_rate(); given invalid currency
	 *
	 * @test
	 * @expectedException \FuelException
	 */
	public function test_fetch_currency_rate_expected_exception_given_invalid_currency()
	{
		Currency::forge(1, 'Foo')->convert_to('USD');
	}

	/**
	 * Test Currency::convert_to(); given invalid currency
	 *
	 * @test
	 * @expectedException \FuelException
	 */
	public function test_convert_to_expected_exception_given_invalid_currency()
	{
		Currency::forge(1, 'USD')->convert_to('Foo');
	}

}