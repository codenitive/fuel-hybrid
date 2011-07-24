<?php

/**
 * Fuel
 *
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
 * Factory class tests
 * 
 * @group Hybrid
 * @group Html
 */
class Test_Html extends \Fuel\Core\TestCase {
	
	public function setup() 
	{
		\Fuel::add_package('hybrid');
		\Config::set('app.site_name', 'FuelPHP');
	}

	public function test_title()
	{
		$expected = '<title>FuelPHP</title>';
		$output = \Hybrid\Html::title();

		$this->assertEquals($expected, $output);

		$expected = '<title>Hello World &mdash; FuelPHP</title>';
		$output = \Hybrid\Html::title('Hello World');

		$this->assertEquals($expected, $output);
	}


}
