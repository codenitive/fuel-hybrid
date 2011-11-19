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
 * @category    Parser_Markdown
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Parser_Markdown extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup()
	{
		\Package::load('hybrid');
	}

	/**
	 * Test Parser_Markdown::parse()
	 *
	 * @test
	 */
	public function test_parse()
	{
		$text = "Hello world

* Thank you";
		$output   = Parser::forge('markdown')->parse($text);
		$expected = "<p>Hello world</p>

<ul>
<li>Thank you</li>
</ul>
";
		$this->assertEquals($expected, $output);
	}
	
}