<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTest;
use function Mathias\ParserCombinator\char;

final class FluidTest extends ParserTest
{
    /** @test */
    public function followedBy()
    {
        $parser = char('a')->followedBy(char('b'));
        $this->assertParse("ab", $parser, "abc");
    }

    /** @test */
    public function into1()
    {
        $parser = char('a')
            ->followedBy(char('b'))
            ->into1('strtoupper');
        $this->assertParse("AB", $parser, "abc");
    }

    /** @test */
    public function intoNew1()
    {
        $parser = char('a')
            ->followedBy(char('b'))
            ->intoNew1(__NAMESPACE__.'\\MyType2');
        $this->assertParse(new MyType2("ab"), $parser, "abc");
    }
}

class MyType2 {
    private $x;

    function __construct($x)
    {
        $this->x = $x;
    }
}