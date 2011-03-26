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
 * @group Core
 * @group Arr
 */
class Test_Factory extends \Fuel\Core\TestCase {
	
	public function setup() {
		\Fuel::add_package('hybrid');
	}

	public function test_language() {
		$expected = \Config::get('language');
		$output = \Hybrid\Factory::get_language();
		
		$this->assertEquals($expected, $output);
	}
	
	public function test_identity() {
		$expected = \Config::get('app.identity');
		$output = \Hybrid\Factory::get_identity();
		
		$this->assertEquals($expected, $output);
	}

}
