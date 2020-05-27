<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{char, crlf, eol, newline, string};

final class CharactersTest extends ParserTestCase
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
        $this->assertNotParse(string('abc'), "babc", "string(abc)");
    }

    /** @test */
    public function newline()
    {
        $this->assertParse("\n", newline(), "\nabc");
        $this->assertNotParse(newline(), "\rabc");
    }

    /** @test */
    public function crlf()
    {
        $this->assertParse("\r\n", crlf(), "\r\nabc");
        $this->assertNotParse(crlf(), "\rabc", "crlf");
        $this->assertNotParse(crlf(), "\rabc", "crlf");
    }

    /** @test */
    public function eol()
    {
        $this->assertParse("\n", eol(), "\nabc");
        $this->assertParse("\r\n", eol(), "\r\nabc");
        $this->assertNotParse(eol(), "\rabc", "eol");
    }

}

