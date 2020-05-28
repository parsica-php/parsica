<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;

final class FluidTest extends ParserTestCase
{
    /** @test */
    public function followedBy()
    {
        $parser = char('a')->followedBy(char('b'));
        $this->assertParse("ab", $parser, "abc");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

    /** @test */
    public function into1()
    {
        $parser = char('a')
            ->followedBy(char('b'))
            ->into1('strtoupper');
        $this->assertParse("AB", $parser, "abc");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

    /** @test */
    public function intoNew1()
    {
        $parser = char('a')
            ->followedBy(char('b'))
            ->intoNew1(__NAMESPACE__.'\\MyType2');
        $this->assertParse(new MyType2("ab"), $parser, "abc");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }
}

class MyType2 {
    private $x;

    function __construct($x)
    {
        $this->x = $x;
    }
}