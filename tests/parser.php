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
 * @category    Parser
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Parser extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup()
	{
		\Package::load('hybrid');
	}

	/**
	 * Test Parser::forge();
	 *
	 * @test
	 */
	public function test_forge()
	{
		$output = Parser::forge('markdown');

		$this->assertTrue($output instanceof \Hybrid\Parser_Markdown);
	}
	
	/**
	 * Test Parser::forge() given invalid driver
	 *
	 * @test
	 * @expectedException \FuelException
	 */
	public function test_forge_expected_exception_given_invalid_driver()
	{
		Parser::forge('html');
	}
	
}