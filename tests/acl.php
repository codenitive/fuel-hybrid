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
 * @category    Acl
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Acl extends \Fuel\Core\TestCase 
{
	/**
	 * Setup the test
	 */
	public function setup()
	{
		\Package::load('hybrid');

		$acl = Acl::forge('mock');
		$acl->add_roles('guest');
		$acl->add_resources(array('blog', 'forum', 'news'));
		$acl->allow('guest', array('blog'), 'view');
		$acl->deny('guest', 'forum');
		
		try
		{
			\Database_Connection::instance(\Config::get('db.active'))->connect();
		}
		catch (\Database_Exception $e)
		{
			// in case when list table is not supported by Database Connection
			$this->markTestSkipped('User table is not available');
		}
	}

	/**
	 * Test Acl::forge()
	 *
	 * @test
	 */
	public function test_forge()
	{
		$output = Acl::forge('mock');

		$this->assertTrue($output instanceof \Hybrid\Acl);
	}

	/**
	 * Test Acl::access();
	 *
	 * @test
	 */
	public function test_access()
	{
		$acl      = Acl::instance('mock');
		
		$expected = true;
		$output   = $acl->access('blog', 'view');
		$this->assertEquals($expected, $output);
		
		$expected = false;
		$output   = $acl->access('blog', 'edit');
		$this->assertEquals($expected, $output);
		
		$expected = false;
		$output   = $acl->access('forum', 'view');
		$this->assertEquals($expected, $output);
		
		$expected = false;
		$output   = $acl->access('news', 'view');
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test Acl::access_status();
	 *
	 * @test
	 */
	public function test_access_status()
	{   
		$acl      = Acl::instance('mock');
		
		$expected = 200;
		$output   = $acl->access_status('blog', 'view');
		$this->assertEquals($expected, $output);
		
		$expected = 401;
		$output   = $acl->access_status('blog', 'edit');
		$this->assertEquals($expected, $output);
		
		$expected = 401;
		$output   = $acl->access_status('forum', 'view');
		$this->assertEquals($expected, $output);
		
		$expected = 401;
		$output   = $acl->access_status('news', 'view');
		$this->assertEquals($expected, $output);
	}
	
}