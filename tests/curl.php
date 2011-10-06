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
 * @category    Curl
 * @category    Test
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Test_Curl extends \Fuel\Core\TestCase {

    /**
     * Setup the test
     */
    public function setup()
    {
        \Package::load('hybrid');
    }

    /**
     * Test Curl::forge();
     *
     * @test
     */
    public function test_forge()
    {
        $output = Curl::forge('GET http://google.com');
        
        $this->assertTrue($output instanceof \Hybrid\Curl); 
    }

    /**
     * Test Curl::forge() given invalid driver
     *
     * @test
     * @expectedException \Fuel_Exception
     */
    public function test_forge_expected_exception_given_invalid_method()
    {
        Curl::forge('FORK http://google.com');
    }
}