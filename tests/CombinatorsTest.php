<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\{char, either, into, seq};

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
            fn($x) => new MyType($x)
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