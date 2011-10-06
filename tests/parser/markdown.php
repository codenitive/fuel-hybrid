<?php

namespace Hybrid;

class Test_Parser_Markdown extends \Fuel\Core\TestCase {
    
    public function setup()
    {
        \Package::load('hybrid');
    }

    public function test_parse()
    {
        $text = "Hello world

* Thank you";
        $output = \Hybrid\Parser::forge('markdown')->parse($text);
        $expected = "<p>Hello world</p>

<ul>
<li>Thank you</li>
</ul>
";

        $this->assertEquals($expected, $output);
    }
}