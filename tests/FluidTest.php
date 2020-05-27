<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinator\char;

final class FluidTest extends ParserTest
{
    /** @test */
    public function followedBy()
    {
        $parser = char('a')->followedBy(char('b'));
        $this->assertParse($parser, "abc", "ab");
    }

    /** @test */
    public function into1()
    {
        $parser = char('a')
            ->followedBy(char('b'))
            ->into1('strtoupper');
        $this->assertParse($parser, "abc", "AB");
    }

    /** @test */
    public function intoNew1()
    {
        $parser = char('a')
            ->followedBy(char('b'))
            ->intoNew1(__NAMESPACE__.'\\MyType2');
        $this->assertParse($parser, "abc", new MyType2("ab"));
    }
}

class MyType2 {
    private $x;

    function __construct($x)
    {
        $this->x = $x;
    }
}