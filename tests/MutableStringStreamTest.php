<?php

namespace Tests\Verraes\Parsica;

use Verraes\Parsica\PHPUnit\ParserAssertions;
use Verraes\Parsica\StringStream;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\alphaNumChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\char;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\either;
use function Verraes\Parsica\string;

final class MutableStringStreamTest extends TestCase
{
    use ParserAssertions;

    public function testEOF()
    {
        $stream = StringStream::fromString("");
        $this->assertTrue($stream->isEOF());

        $stream = StringStream::fromString("a");
        $this->assertFalse($stream->isEOF());

        $stream = StringStream::fromString("abc");
        $result = $stream->take1();
        $this->assertEquals('a', $result);
        $this->assertEquals('bc', $stream->peakAll());
        $this->assertFalse($stream->isEOF());

        $result = $stream->take1();
        $this->assertEquals('b', $result);
        $this->assertEquals('c', $stream->peakAll());
        $this->assertFalse($stream->isEOF());

        $result = $stream->take1();
        $this->assertEquals('c', $result);
        $this->assertEquals('', $stream->peakAll());
        $this->assertTrue($stream->isEOF());
    }


    public function testEOFStringStream()
    {
        $stream = StringStream::fromString("");
        $this->assertTrue($stream->isEOF());

        $stream = StringStream::fromString("a");
        $this->assertFalse($stream->isEOF());

        $stream = StringStream::fromString("abc");
        $stream->take1();
        $stream->take1();
        $this->assertFalse($stream->isEOF());
        $stream->take1();
        $this->assertTrue($stream->isEOF());
    }

    /** @test */
    public function either_with_mixed_type()
    {
        $parser = either(
            atLeastOne(digitChar())->map(fn(string $o)=> (int) $o)->thenEof(),
            atLeastOne(alphaNumChar())->thenEof(),
        );

        $this->assertTrue($parser->run(StringStream::fromString(''))->isFail());

        $actual = $parser->tryString("1")->output();
        $this->assertIsInt($actual);
        $this->assertSame(1, $actual);

        $actual = $parser->tryString("a")->output();
        $this->assertIsString($actual);
        $this->assertEquals("a", $actual);
    }


    /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));
        $this->assertParses("a", $parser, "a");
        $this->assertParses("b", $parser, "b");

    }
}
