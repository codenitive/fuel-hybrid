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
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Uri extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup() 
	{
		\Package::load('hybrid');

		$_GET = array(
			'hello' => 'world',
			'foo'   => 'bar',
			'fuel'  => 'php',
		);
	}

	/**
	 * Test Uri::build_get_query()
	 *
	 * @test
	 */
	public function test_build_get_query() 
	{
		$output = \Hybrid\Uri::build_get_query(array('foo', 'hello'));
		$expected = '?foo=bar&hello=world';
		$this->assertEquals($expected, $output);

		$output = \Hybrid\Uri::build_get_query(array('foo', 'unavailable'));
		$expected = '?foo=bar';
		$this->assertEquals($expected, $output);
	
		$output = \Hybrid\Uri::build_get_query(array('unavailable'));
		$expected = '?';
		$this->assertEquals($expected, $output);
	}

}