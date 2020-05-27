<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\digit;
use function Mathias\ParserCombinator\float;
use function Mathias\ParserCombinator\string;

final class BasicParsersTest extends ParserTest
{
    /** @test */
    public function char()
    {
        $this->shouldParse(char('a'), "abc", "a");
        $this->shouldNotParse(char('a'), "bc", "char(a)");
    }

    /** @test */
    public function string()
    {
        $this->shouldParse(string('abc'), "abcde", "abc");
        $this->shouldNotParse(string('abc'), "babc");
    }

    /** @test */
    public function digit()
    {
        $this->shouldParse(digit(), "1ab", "1");
    }

    /** @test */
    public function float()
    {
        $this->shouldParse(float(), "0", "0");
        $this->shouldParse(float(), "0.1", "0.1");
        $this->shouldParse(float(), "123.456", "123.456");
        $this->shouldParse(float()->into1('floatval'), "123.456", 123.456);
    }
}
