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
 * @category    Auth
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Auth extends \Fuel\Core\TestCase 
{

	/**
	 * Setup the test
	 */
	public function setup()
	{
		\Package::load('hybrid');
		\Config::load('autho', 'autho');
		\Config::set('autho.hash_type', 'sha1');
		\Config::set('autho.salt', '12345');
	}

	/**
	 * Test Auth::create_hash();
	 *
	 * @test
	 */
	public function test_create_hash()
	{
		$string   = 'helloworld123';
		$expected = sha1('12345'.$string);
		$output   = Auth::create_hash($string);

		$this->assertEquals($expected, $output);
	}
	
}