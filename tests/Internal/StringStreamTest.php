<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Internal;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\isEqual;
use function Verraes\Parsica\notPred;

final class StringStreamTest extends TestCase
{
    /** @test */
    public function take1()
    {
        $stream = StringStream::fromString("abc");
        $this->assertEquals(0, $stream->position()->pointer());
        $t = $stream->take1();
        $this->assertEquals("a", $t);
        $this->assertEquals(1, $stream->position()->pointer());
        $t = $stream->take1();
        $this->assertEquals("b", $t);
        $this->assertEquals(2, $stream->position()->pointer());
        $this->assertEquals("c", (string)$stream->peak1());
    }

    /** @test */
    public function takeN()
    {
        $stream = StringStream::fromString("abcde");
        $t = $stream->takeN(3);
        $this->assertEquals("abc", $t);
        $this->assertEquals(3, $stream->position()->pointer());
        $this->assertEquals("de", $stream->peakN(2));
    }

    /** @test */
    public function takeWhile()
    {
        $stream = StringStream::fromString("abc\nde");
        $tokens = $stream->takeWhile(fn($c) => $c !== "\n");
        $this->assertEquals("abc", $tokens);
        $this->assertEquals(3, $stream->position()->pointer());
        $this->assertEquals("\nde", $stream->peakN(3));
    }

    /** @test */
    public function commit_a_transaction()
    {
        $stream = StringStream::fromString("abc123");

        $stream->beginTransaction();
        $stream->take1();
        $stream->commit();

        $token = $stream->take1();

        $this->assertEquals('b', $token);
        $this->assertEquals('c123', $stream->peakWhile(fn()=>true));
    }

    /** @test */
    public function rollback_a_transaction()
    {
        $stream = StringStream::fromString("abc123");

        $stream->beginTransaction();
        $stream->take1();
        $stream->rollback();

        $token = $stream->take1();

        $this->assertEquals('a', $token);
        $this->assertEquals('bc123', $stream->peakAll());
    }

    /** @test */
    public function rollback_a_transaction_at_EOF()
    {
        $stream = StringStream::fromString("ab");

        $stream->beginTransaction();
        $stream->take1();
        $this->assertFalse($stream->isEOF());
        $stream->rollback();
        $this->assertFalse($stream->isEOF());

        $stream->beginTransaction();
        $stream->take1();
        $stream->take1();
        $this->assertTrue($stream->isEOF());
        $stream->rollback();
        $this->assertFalse($stream->isEOF());


        $token = $stream->take1();

        $this->assertEquals('a', $token);
        $this->assertEquals('b', $stream->peakAll());
    }

    /** @test */
    public function peak()
    {
        $stream = StringStream::fromString("abc");

        $this->assertEquals("a", $stream->peak1());
        $this->assertEquals(0, $stream->position()->pointer());
        $this->assertEquals(1, $stream->position()->line());
        $this->assertEquals(1, $stream->position()->column());
        $this->assertEquals("abc", $stream->peakN(3));
        $this->assertEquals(0, $stream->position()->pointer());
        $this->assertEquals("ab", $stream->peakWhile(notPred(isEqual("c"))));
        $this->assertEquals(0, $stream->position()->pointer());

        $_ = $stream->take1();
        $this->assertEquals(1, $stream->position()->pointer());
        $this->assertEquals(2, $stream->position()->column());
        $this->assertEquals(1, $stream->position()->line());
        $this->assertEquals("b", $stream->peak1());
        $this->assertEquals(1, $stream->position()->pointer());
        $this->assertEquals(2, $stream->position()->column());
        $this->assertEquals(1, $stream->position()->line());

    }

    /** @test */
    public function peakBack()
    {
        $stream = StringStream::fromString("abc");
        $_ = $stream->take1();
        $this->assertEquals("b", $stream->peak1());
        $this->assertEquals("a", $stream->peakBack());

        $stream = StringStream::fromString("abc");
        $this->assertEquals("a", $stream->peak1());
        $this->assertEquals("b", $stream->peakBack());

        // empty string, string of 1, transactions, first char
    }


}
