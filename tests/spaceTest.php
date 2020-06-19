<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica;

use Verraes\Parsica\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\{crlf, eol, newline, skipHSpace, skipHSpace1, skipSpace, skipSpace1, tab};

final class spaceTest extends TestCase
{
    use ParserAssertions;

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
        $this->assertRemain("no space", skipSpace(), "no space");
        $this->assertRemain("1 space", skipSpace(), " 1 space");
        $this->assertRemain("tab", skipSpace(), "\ttab");
        $this->assertRemain("newline", skipSpace(), "\nnewline");
        $this->assertRemain("abc", skipSpace(), "\t   \n   \r\n  abc");
    }

    /** @test */
    public function skipHSpace()
    {
        $this->assertRemain("abc", skipHSpace(), "abc");
        $this->assertRemain("abc", skipHSpace(), "\t   abc");
        $this->assertRemain("\nabc", skipHSpace(), "\t   \nabc");
    }

    /** @test */
    public function skipSpace1()
    {
        $this->assertNotParse(skipSpace1(), "no space", "skipSpace1");
        $this->assertRemain("1 space", skipSpace1(), " 1 space");
        $this->assertRemain("tab", skipSpace1(), "\ttab");
        $this->assertRemain("newline", skipSpace1(), "\nnewline");
        $this->assertRemain("abc", skipSpace1(), "\t   \n   \r\n  abc");
    }


    /** @test */
    public function skipHSpace1()
    {
        $this->assertNotParse(skipHSpace1(), "no space", "skipHSpace1");
        $this->assertRemain("some space", skipHSpace1(), "\t   some space");
        $this->assertRemain("abc", skipHSpace1(), "\t   abc");
        $this->assertRemain("\nabc", skipHSpace1(), "\t   \nabc");
    }
}

