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
 * @category    Html
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Html extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup() 
	{
		\Package::load('hybrid');
		\Config::set('app.site_name', 'FuelPHP');
	}

	/**
	 * Test Html::title();
	 *
	 * @test
	 */
	public function test_title()
	{
		$expected = '<title>FuelPHP</title>';
		$output   = Html::title();
		$this->assertEquals($expected, $output);

		$expected = '<title>Hello World &mdash; FuelPHP</title>';
		$output   = Html::title('Hello World');
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Html::br();
	 *
	 * @test
	 */
	public function test_br()
	{
		$expected = '<br /><br />';
		$output   = Html::br(2);
		$this->assertEquals($expected, $output);

		$expected = '<br class="clearfix" />';
		$output   = Html::br(1, array('class' => 'clearfix'));
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Html::hr();
	 *
	 * @test
	 */
	public function test_hr()
	{
		$expected = '<hr />';
		$output   = Html::hr();
		$this->assertEquals($expected, $output);

		$expected = '<hr class="clearfix" />';
		$output   = Html::hr(array('class' => 'clearfix'));
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Html::nbs();
	 *
	 * @test
	 */
	public function test_nbs()
	{
		$expected = '&nbsp;&nbsp;&nbsp;';
		$output   = Html::nbs(3);
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Html::header(); using HTML5
	 *
	 * @test
	 */
	public function test_header_html5()
	{
		Html::$html5 = true;

		$expected = '<header>Hello World</header>';
		$output   = Html::header('Hello World');
		$this->assertEquals($expected, $output);

		$expected = '<header class="title">Hello World</header>';
		$output   = Html::header('Hello World', array('class' => 'title'));
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Html::header(); not using HTML5
	 *
	 * @test
	 */
	public function test_header_not_html5()
	{
		Html::$html5 = false;

		$expected = '<div id="header">Hello World</div>';
		$output   = Html::header('Hello World');
		$this->assertEquals($expected, $output);

		$expected = '<div id="header" class="title">Hello World</div>';
		$output   = Html::header('Hello World', array('class' => 'title'));
		$this->assertEquals($expected, $output);

		$expected = '<div id="not-header" class="title">Hello World</div>';
		$output   = Html::header('Hello World', array('class' => 'title', 'id' => 'not-header'));
		$this->assertEquals($expected, $output);
	}
	
}