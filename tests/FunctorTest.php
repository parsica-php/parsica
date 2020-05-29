<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{char, seq};

final class FunctorTest extends ParserTestCase
{
    /** @test */
    public function fmap()
    {
        $parser =
            char('a')->followedBy(char('b'))
                ->fmap('strtoupper');

        $expected = "AB";

        $this->assertParse($expected, $parser, "abca");
    }


    /** @test */
    public function fmapClass()
    {
        $parser = seq(char('a'), char('b'))
            ->fmapClass(__NAMESPACE__ . '\\MyType1');

        $expected = new MyType1("ab");

        $this->assertParse($expected, $parser, "abc");
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