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
 * Chart class tests
 * 
 * @group Hybrid
 * @group Config
 */
class Test_Config extends \Fuel\Core\TestCase 
{
 	
 	public function setup()
 	{
 		\Fuel::add_package('hybrid');
 	}

 	public function test_register_driver()
 	{
		$output = \Hybrid\Config::register_driver('testdriver');
		
		$this->assertArrayHasKey('testdriver');
		$this->assertContains('Config_Testdriver', \Hybrid\Config::available_drivers());
		
		$output = \Hybrid\Config::register_driver('undefineddriver');
		$this->assertFalse($output);
 	}
	
	public function test_unregister_driver()
 	{
 		$output = \Hybrid\Config::unregister_driver('undefineddriver');
		$this->assertFalse($output);

		\Hybrid\Config::register_driver('testdriver');
		$output = \Hybrid\Config::unregister_driver('testdriver');
		$this->assertTrue($output);
		
		$output = false;
		if(array_key_exists('testdriver', \Hybrid\Config::available_drivers()))
		{
			$output = true;
		}
		
		$this->asserFalse($output);
		
 	}
	
	public function test_available_drivers()
	{
		\Hybrid\Config::register_driver('testdriver');
		
		$output = \Hybrid\Config::available_drivers();
		
		$this->assertContains('Config_Testdriver', $output);
		$this->assertArrayHasKey('redis', $output);
		$this->assertArrayHasKey('testdriver', $output);
	}
}