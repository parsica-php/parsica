<?php

namespace Tests\Verraes\Parsica;

use Verraes\Parsica\StringStream;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\alphaNumChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\either;

final class MutableStringStreamTest extends TestCase
{
    public function testTake1()
    {
        $stream = new StringStream("abc123");

        $takeResult = $stream->take1();

        $this->assertEquals('a', $takeResult->chunk());
        $this->assertEquals('bc123', (string) $takeResult->stream());
    }

    public function testTransactionCommit()
    {
        $stream = new StringStream("abc123");

        $stream->beginTransaction();
        $stream->take1();
        $stream->commit();

        $takeResult = $stream->take1();

        $this->assertEquals('b', $takeResult->chunk());
        $this->assertEquals('c123', (string) $takeResult->stream());
    }

    public function testTransactionRollback()
    {
        $stream = new StringStream("abc123");

        $stream->beginTransaction();
        $stream->take1();
        $stream->rollback();

        $takeResult = $stream->take1();

        $this->assertEquals('a', $takeResult->chunk());
        $this->assertEquals('bc123', (string) $takeResult->stream());
    }

    public function testTransactionRollbackEOF()
    {
        $stream = new StringStream("ab");

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


        $takeResult = $stream->take1();

        $this->assertEquals('a', $takeResult->chunk());
        $this->assertEquals('bc', (string) $takeResult->stream());
    }

    public function testEOF()
    {
        $stream = new StringStream("");
        $this->assertTrue($stream->isEOF());

        $stream = new StringStream("a");
        $this->assertFalse($stream->isEOF());

        $stream = new StringStream("abc");
        $result = $stream->take1();
        $this->assertEquals('a', $result->chunk());
        $this->assertEquals('bc', (string) $result->stream());
        $this->assertFalse($stream->isEOF());

        $result = $stream->take1();
        $this->assertEquals('b', $result->chunk());
        $this->assertEquals('c', (string) $result->stream());
        $this->assertFalse($stream->isEOF());

        $result = $stream->take1();
        $this->assertEquals('c', $result->chunk());
        $this->assertEquals('', (string) $result->stream());
        $this->assertTrue($stream->isEOF());
    }


    public function testEOFStringStream()
    {
        $stream = new StringStream("");
        $this->assertTrue($stream->isEOF());

        $stream = new StringStream("a");
        $this->assertFalse($stream->isEOF());

        $stream = new StringStream("abc");
        $stream = $stream->take1()->stream();
        $stream = $stream->take1()->stream();
        $this->assertFalse($stream->isEOF());
        $stream = $stream->take1()->stream();
        $this->assertTrue($stream->isEOF());
    }

    /** @test */
    public function either_with_mixed_type()
    {
        $parser = either(
            atLeastOne(digitChar())->map(fn(string $o)=> (int) $o)->thenEof(),
            atLeastOne(alphaNumChar())->thenEof(),
        );

        $this->assertTrue($parser->run(new StringStream(''))->isFail());

        $actual = $parser->tryString("1")->output();
        $this->assertIsInt($actual);
        $this->assertSame(1, $actual);

        $actual = $parser->tryString("a")->output();
        $this->assertIsString($actual);
        $this->assertEquals("a", $actual);
    }
}
