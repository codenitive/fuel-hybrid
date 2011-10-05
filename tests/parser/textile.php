<?php

namespace Hybrid;

class Test_Parser_Textile extends \Fuel\Core\TestCase {
    
    public function setup()
    {
        \Fuel::add_package('hybrid');
    }

    public function test_parse()
    {
        $text = '*hellow*';
        $output = \Hybrid\Parser::forge('textile')->parse($text);;
        $expected = "	<p><strong>hellow</strong></p>";

        $this->assertEquals($expected, $output);
    }
}