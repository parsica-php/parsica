<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\{char, either, into, intoNew, seq};

final class CombinatorsTest extends ParserTest
{
    /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));

        $this->shouldParse($parser, "abc", "a");
        $this->shouldParse($parser, "bc", "b");
        $this->shouldNotParse($parser, "cd");
    }

    /** @test */
    public function seq()
    {
        $parser = seq(char('a'), char('b'));

        $this->shouldParse($parser, "abc", "ab");
        $this->shouldNotParse($parser, "acc");
        $this->shouldNotParse($parser, "cab");

    }

    /** @test */
    public function into()
    {
        $parser = into(
            seq(char('a'), char('b')),
            'strtoupper'
        );

        $expected = "AB";

        $this->shouldParse($parser, "abc", $expected);
    }
    
    
    /** @test */
    public function intoNew()
    {
        $parser = intoNew(
            seq(char('a'), char('b')),
            __NAMESPACE__.'\\MyType'
        );

        $expected = new MyType("ab");

        $this->shouldParse($parser, "abc", $expected);
    }

}

class MyType
{
    private $val;

    function __construct($val)
    {
        $this->val = $val;
    }
}