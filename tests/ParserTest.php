<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;

final class ParserTest extends ParserTestCase
{
    /** @test */
    public function followedBy()
    {
        $parser = char('a')->followedBy(char('b'));
        $this->assertParse("ab", $parser, "abc");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

    /** @test */
    public function fmap()
    {
        $parser = char('a')
            ->followedBy(char('b'))
            ->fmap('strtoupper');
        $this->assertParse("AB", $parser, "abc");
    }

}

class MyType2 {
    private $x;

    function __construct($x)
    {
        $this->x = $x;
    }
}