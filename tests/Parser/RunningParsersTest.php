<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica\Parser;

use Verraes\Parsica\Internal\Fail;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\char;
use function Verraes\Parsica\skipSpace;
use function Verraes\Parsica\string;

final class RunningParsersTest extends TestCase
{
    /** @test */
    public function try_throws()
    {
        $parser = char('a');
        $result = $parser->try("a");
        $this->assertSame("a", $result->output());

        $this->expectException(Fail::class);
        $result = $parser->try("b");
    }

    /** @test */
    public function continueFrom()
    {
        $parser = string('hello')->sequence(skipSpace());
        $result = $parser->try("hello world!");
        $parser2 = string("world");
        $result2 = $parser2->continueFrom($result);
        $this->assertEquals("world", $result2->output());
        $this->assertEquals("!", $result2->remainder());
    }
}
