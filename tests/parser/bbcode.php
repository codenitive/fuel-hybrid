<?php

namespace Hybrid;

class Test_Parser_Bbcode extends \Fuel\Core\TestCase {
    
    public function setup()
    {
        \Fuel::add_package('hybrid');
    }

    public function test_parse()
    {
        $text = "[b]strong[/b][i]italic[/i]";
        $output = \Hybrid\Parser::forge('BBCode')->parse($text);
        $expected = "<b>strong</b><i>italic</i>";

        $this->assertEquals($expected, $output);
    }
}