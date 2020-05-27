<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTest;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\crlf;
use function Mathias\ParserCombinator\newline;
use function Mathias\ParserCombinator\string;

final class CharactersTest extends ParserTest
{
    /** @test */
    public function char()
    {
        $this->assertParse("a", char('a'), "abc");
        $this->assertRemain("bc", char('a'), "abc");
        $this->assertNotParse(char('a'), "bc", "char(a)");
    }

    /** @test */
    public function string()
    {
        $this->assertParse("abc", string('abc'), "abcde");
        $this->assertNotParse(string('abc'), "babc");
    }

    /** @test */
    public function newline()
    {
        $this->assertParse("\n", newline(), "\nabc");
    }

    /** @test */
    public function crlf()
    {
        $this->assertParse("\r\n", crlf(), "\r\nabc");
    }
}

