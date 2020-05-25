<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\char;

final class FluidTest extends ParserTest
{
    /** @test */
    public function seq()
    {
        $parser = char('a')->seq(char('b'));
        $this->shouldParse($parser, "abc", "ab");
    }

    /** @test */
    public function into()
    {
        $parser = char('a')
            ->seq(char('b'))
            ->into('strtoupper');
        $this->shouldParse($parser, "abc", "AB");
    }

    /** @test */
    public function intoNew()
    {
        $parser = char('a')
            ->seq(char('b'))
            ->intoNew(__NAMESPACE__.'\\MyType2');
        $this->shouldParse($parser, "abc", new MyType2("ab"));
    }
}

class MyType2 {
    private $x;

    function __construct($x)
    {
        $this->x = $x;
    }
}