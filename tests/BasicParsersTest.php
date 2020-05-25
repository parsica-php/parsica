<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\char;
use function Mathias\ParserCombinators\string;

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

}
