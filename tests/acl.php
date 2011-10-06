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
 * Authentication Class
 * 
 * Why another class? FuelPHP does have it's own Auth package but what Hybrid does 
 * it not defining how you structure your database but instead try to be as generic 
 * as possible so that we can support the most basic structure available
 * 
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Acl
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Acl extends \Fuel\Core\TestCase {
    
    private $enable_test = true;

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
        $user_table = \DB::list_tables('users');

        if(empty($user_table))
        {
            $this->enable_test = false;
        }
    }

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
        if ( ! $this->enable_test)
        {
            return;
        }

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
        if ( ! $this->enable_test)
        {
            return;
        }
        
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