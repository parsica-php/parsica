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
use Verraes\Parsica\Internal\Position;
use Verraes\Parsica\MBStringStream;

final class StringStreamTest extends TestCase
{

    /** @test */
    public function take1()
    {
        $s = new MBStringStream("abc");
        $t = $s->take1();
        $this->assertEquals("a", $t->chunk());
        $expectedPosition = new Position("<input>", 1, 2);
        $expectedStream = new MBStringStream("bc", $expectedPosition);
        $this->assertEquals($expectedStream, $t->stream());
    }

    /** @test */
    public function takeN()
    {
        $s = new MBStringStream("abcde");
        $t = $s->takeN(3);
        $this->assertEquals("abc", $t->chunk());
        $expectedPosition = new Position("<input>", 1, 4);
        $expectedStream = new MBStringStream("de", $expectedPosition);
        $this->assertEquals($expectedStream, $t->stream());
    }

    /** @test */
    public function takeWhile()
    {
        $s = new MBStringStream("abc\nde");
        $t = $s->takeWhile(fn($c) => $c !== "\n");
        $this->assertEquals("abc", $t->chunk());
        $expectedPosition = new Position("<input>", 1, 4);
        $expectedStream = new MBStringStream("\nde", $expectedPosition);
        $this->assertEquals($expectedStream, $t->stream());
    }
}
