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
use Verraes\Parsica\TextFileStream;

final class TextFileStreamTest extends TestCase
{

    private string $stubBasePath;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->stubBasePath = dirname(__DIR__) . "/stubs/";
    }

    /** @test */
    public function take1()
    {
        $testStub = $this->stubBasePath . "abc.txt";

        $s = new TextFileStream($testStub);
        $t = $s->take1();
        $this->assertEquals("a", $t->chunk());
        $expectedPosition = new Position($testStub, 1, 2, 1);
        $expectedStream = new TextFileStream($testStub, $expectedPosition);
        // Because the file socket stream is unique we compare individual aspects...
        $this->assertEquals($expectedStream->position(), $t->stream()->position());
        $this->assertEquals($expectedStream->filePath(), $t->stream()->filePath());
        $this->assertEquals((string) $expectedStream, (string) $t->stream());
    }

    /** @test */
    public function takeN()
    {
        $testStub = $this->stubBasePath . "abcde.txt";
        $s = new TextFileStream($testStub);
        $t = $s->takeN(3);
        $this->assertEquals("abc", $t->chunk());
        $expectedPosition = new Position($testStub, 1, 4, 3);
        $expectedStream = new TextFileStream($testStub, $expectedPosition);
        $this->assertEquals($expectedStream->position(), $t->stream()->position());
        $this->assertEquals($expectedStream->filePath(), $t->stream()->filePath());
        $this->assertEquals((string) $expectedStream, (string) $t->stream());
    }

    /** @test */
    public function takeWhile()
    {
        $testStub = $this->stubBasePath . "abc-return-de.txt";
        $s = new TextFileStream($testStub);
        $t = $s->takeWhile(fn($c) => $c !== "\n");
        $this->assertEquals("abc", $t->chunk());
        $expectedPosition = new Position($testStub, 1, 4, 3);
        $expectedStream = new TextFileStream($testStub, $expectedPosition);
        $this->assertEquals($expectedStream->position(), $t->stream()->position());
        $this->assertEquals($expectedStream->filePath(), $t->stream()->filePath());
        $this->assertEquals((string) $expectedStream, (string) $t->stream());
    }
}
