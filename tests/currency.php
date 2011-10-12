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
 * @category    Factory
 * @category    Test
 * @author      Ignacio Muñoz Fernandez <nmunozfernandez@gmail.com>
 */

class Test_Currency extends \Fuel\Core\TestCase 
{
    /**
     * Setup the test
     */
    public function setup()
    {
        \Package::load('hybrid');
    }
    
    public function test_forge()
    {
        //@todo make the test when the curl class is released
    }

}