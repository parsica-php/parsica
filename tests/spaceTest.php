<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{crlf, eol, newline, skipHSpace, skipHSpace1, skipSpace, skipSpace1, tab};

final class spaceTest extends ParserTestCase
{
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

    /** @test */
    public function tab()
    {
        $this->assertParse("\t", tab(), "\tabc");
        $this->assertNotParse(tab(), "abc", "tab");
    }

    /** @test */
    public function skipSpace()
    {
        $this->assertParse("", skipSpace(), "no space");
        $this->assertParse("", skipSpace(), " 1 space");
        $this->assertParse("", skipSpace(), "\ttab");
        $this->assertParse("", skipSpace(), "\nnewline");
        $this->assertParse("", skipSpace(), "\t   \n   \r\n  abc");
        $this->assertRemain("abc", skipSpace(), "\t   \n   \r\n  abc");
    }

    /** @test */
    public function skipHSpace()
    {
        $this->assertParse("", skipHSpace(), "no space");
        $this->assertParse("", skipHSpace(), "\t   some space");
        $this->assertRemain("abc", skipHSpace(), "\t   abc");
        $this->assertRemain("\nabc", skipHSpace(), "\t   \nabc");
    }

    /** @test */
    public function skipSpace1()
    {
        $this->assertNotParse(skipSpace1(), "no space", "skipSpace1");
        $this->assertParse("", skipSpace1(), " 1 space");
        $this->assertParse("", skipSpace1(), "\ttab");
        $this->assertParse("", skipSpace1(), "\nnewline");
        $this->assertParse("", skipSpace1(), "\t   \n   \r\n  abc");
        $this->assertRemain("abc", skipSpace1(), "\t   \n   \r\n  abc");
    }


    /** @test */
    public function skipHSpace1()
    {
        $this->assertNotParse(skipHSpace1(), "no space", "skipHSpace1");
        $this->assertParse("", skipHSpace1(), "\t   some space");
        $this->assertRemain("abc", skipHSpace1(), "\t   abc");
        $this->assertRemain("\nabc", skipHSpace1(), "\t   \nabc");
    }
}

