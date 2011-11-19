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
 * @category    Parser_Bbcode
 * @category    Test
 * @author      Ignacio Muñoz Fernandez <nmunozfernandez@gmail.com>
 */

class Test_Parser_Bbcode extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup()
	{
		\Fuel::add_package('hybrid');
	}

	/**
	 * Test Parser_Bbcode::parse();
	 *
	 * @test
	 */
	public function test_parse()
	{
		$text     = "[b]strong[/b][i]italic[/i]";
		$output   = Parser::forge('Bbcode')->parse($text);
		$expected = "<b>strong</b><i>italic</i>";

		$this->assertEquals($expected, $output);
	}
	
}