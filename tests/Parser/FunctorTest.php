<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\float;
use function Mathias\ParserCombinator\sequence;

final class FunctorTest extends ParserTestCase
{
    /** @test */
    public function fmap()
    {
        $parser =
            char('a')->followedBy(char('b'))
                ->fmap('strtoupper');

        $expected = "B";

        $this->assertParse($expected, $parser, "abca");
    }

    /** @test */
    public function fmapClass()
    {
        $parser = sequence(char('a'), char('b'))
            ->fmapClass(__NAMESPACE__ . '\\MyType1');

        $expected = new MyType1("b");

        $this->assertParse($expected, $parser, "abc");
    }

    /** @test */
    public function simple_eur()
    {
        $parser = sequence(
            char('€'),
            float()->fmap('floatval')->fmapClass(SimpleEur::class)
        );
        $this->assertParse(new SimpleEur(1.25), $parser, "€1.25");

    }
}

class MyType1
{
    private $val;

    public function __construct($val)
    {
        $this->val = $val;
    }
}


final class SimpleEur
{
    private float $val;

    public function __construct(float $val)
    {
        $this->val = $val;
    }

}
