<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\{char, either, ignore, into1, intoNew1, optional, seq, string};

final class TypecastingTest extends ParserTest
{
    /** @test */
    public function into1()
    {
        $parser = into1(
            seq(char('a'), char('b')),
            'strtoupper'
        );

        $expected = "AB";

        $this->shouldParse($parser, "abc", $expected);
    }
    
    
    /** @test */
    public function intoNew()
    {
        $parser = intoNew1(
            seq(char('a'), char('b')),
            __NAMESPACE__.'\\MyType1'
        );

        $expected = new MyType1("ab");

        $this->shouldParse($parser, "abc", $expected);
    }

}

class MyType1
{
    private $val;

    function __construct($val)
    {
        $this->val = $val;
    }
}