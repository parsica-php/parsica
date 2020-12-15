<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\Internal;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Internal\Position;
use Parsica\Parsica\StringStream;

final class StringStreamTest extends TestCase
{

    /** @test */
    public function take1()
    {
        $s = new StringStream("abc");
        $t = $s->take1();
        $this->assertEquals("a", $t->chunk());
        $expectedPosition = new Position(3, "<input>", 1, 2, 1);
        $expectedStream = new StringStream("bc", $expectedPosition);
        $this->assertEquals($expectedStream, $t->stream());
    }

    /** @test */
    public function takeN()
    {
        $s = new StringStream("abcde");
        $t = $s->takeN(3);
        $this->assertEquals("abc", $t->chunk());
        $expectedPosition = new Position(5, "<input>", 1, 4, 3);
        $expectedStream = new StringStream("de", $expectedPosition);
        $this->assertEquals($expectedStream, $t->stream());
    }

    /** @test */
    public function takeWhile()
    {
        $s = new StringStream("abc\nde");
        $t = $s->takeWhile(fn($c) => $c !== "\n");
        $this->assertEquals("abc", $t->chunk());
        $expectedPosition = new Position(6, "<input>", 1, 4, 3);
        $expectedStream = new StringStream("\nde", $expectedPosition);
        $this->assertEquals($expectedStream, $t->stream());
    }
}
