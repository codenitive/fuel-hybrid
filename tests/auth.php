<?php

namespace Autho;

class Test_Auth extends \Fuel\Core\TestCase {

    public function setup()
    {
        \Fuel::add_package('autho');
        \Config::load('autho', 'autho');
        \Config::set('autho.salt', '12345');
    }

    public function test_add_salt()
    {
        $string   = 'helloworld123';
        $expected = sha1('12345' . $string);
        $output   = Auth::add_salt($string);

        $this->assertEquals($expected, $output);
    }
}