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
 * Uri class tests
 * 
 * @group Hybrid
 * @group Uri
 */
class Test_Uri extends \Fuel\Core\TestCase {
    
    public function setup() 
    {
        \Fuel::add_package('hybrid');

        $_GET = array(
            'hello' => 'world',
            'foo'   => 'bar',
            'fuel'  => 'php',
        );
    }

    public function test_build_get_query() 
    {
        $output = \Hybrid\Uri::build_get_query(array('foo', 'hello'));
        $expected = '?foo=bar&hello=world';

        $this->assertEquals($expected, $output);

        $output = \Hybrid\Uri::build_get_query(array('foo', 'unavailable'));
        $expected = '?foo=bar';

        $this->assertEquals($expected, $output);
    }
    public function test_build_get_query_given_empty() 
    {
        $output = \Hybrid\Uri::build_get_query(array('unavailable'));
        $expected = '?';

        $this->assertEquals($expected, $output);
    }

}