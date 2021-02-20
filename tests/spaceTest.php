<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\{crlf, eol, newline, skipHSpace, skipHSpace1, skipSpace, skipSpace1, tab};

final class spaceTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function newline()
    {
        $this->assertParses("\nabc", newline(), "\n");
        $this->assertParseFails("\rabc", newline());
    }

    /** @test */
    public function crlf()
    {
        $this->assertParses("\r\nabc", crlf(), "\r\n");
        $this->assertParseFails("\rabc", crlf(), "<crlf>");
        $this->assertParseFails("\rabc", crlf(), "<crlf>");
    }

    /** @test */
    public function eol()
    {
        $this->assertParses("\nabc", eol(), "\n");
        $this->assertParses("\r\nabc", eol(), "\r\n");
        $this->assertParseFails("\rabc", eol(), "<EOL>");
    }

    /** @test */
    public function tab()
    {
        $this->assertParses("\tabc", tab(), "\t");
        $this->assertParseFails("abc", tab(), "<tab>");
    }

    /** @test */
    public function skipSpace()
    {
        $this->assertRemainder("no space", skipSpace(), "no space");
        $this->assertRemainder(" 1 space", skipSpace(), "1 space");
        $this->assertRemainder("\ttab", skipSpace(), "tab");
        $this->assertRemainder("\nnewline", skipSpace(), "newline");
        $this->assertRemainder("\t   \n   \r\n  abc", skipSpace(), "abc");
    }

    /** @test */
    public function skipHSpace()
    {
        $this->assertRemainder("abc", skipHSpace(), "abc");
        $this->assertRemainder("\t   abc", skipHSpace(), "abc");
        $this->assertRemainder("\t   \nabc", skipHSpace(), "\nabc");
    }

    /** @test */
    public function skipSpace1()
    {
        $this->assertParseFails("no space", skipSpace1(), "<space>");
        $this->assertRemainder(" 1 space", skipSpace1(), "1 space");
        $this->assertRemainder("\ttab", skipSpace1(), "tab");
        $this->assertRemainder("\nnewline", skipSpace1(), "newline");
        $this->assertRemainder("\t   \n   \r\n  abc", skipSpace1(), "abc");
    }


    /** @test */
    public function skipHSpace1()
    {
        $this->assertParseFails("no space", skipHSpace1(), "<space>");
        $this->assertRemainder("\t   some space", skipHSpace1(), "some space");
        $this->assertRemainder("\t   abc", skipHSpace1(), "abc");
        $this->assertRemainder("\t   \nabc", skipHSpace1(), "\nabc");
    }
}

