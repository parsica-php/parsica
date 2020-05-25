<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\{char, either, ignore, into1, intoNew1, optional, seq, string};

final class CombinatorsTest extends ParserTest
{

    /** @test */
    public function ignore()
    {
        $parser = ignore(char('a'));
        $this->shouldParse($parser, "abc", "", "bc");

        $parser = string('abcd')
            ->seq(ignore(char('-')))
            ->seq(string('efgh'));
        $this->shouldParse($parser, "abcd-efgh", "abcdefgh");

    }

    /** @test */
    public function optional()
    {
        $parser = char('a')->optional();
        $this->shouldParse($parser, "abc", "a", "bc");
        $this->shouldParse($parser, "bc", "", "bc");

        $parser = string('abcd')
            ->seq(optional(ignore(char('-'))))
            ->seq(string('efgh'));
        $this->shouldParse($parser, "abcd-efgh", "abcdefgh");
        $this->shouldParse($parser, "abcdefgh", "abcdefgh");

    }


        /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));

        $this->shouldParse($parser, "abc", "a", "bc");
        $this->shouldParse($parser, "bc", "b", "c");
        $this->shouldNotParse($parser, "cd");
    }

    /** @test */
    public function seq()
    {
        $parser = seq(char('a'), char('b'));

        $this->shouldParse($parser, "abc", "ab", "c");
        $this->shouldNotParse($parser, "acc");
        $this->shouldNotParse($parser, "cab");

    }


}
