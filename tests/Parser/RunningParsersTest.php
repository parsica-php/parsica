<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\ParseResult\ParseFailure;
use PHPUnit\Framework\TestCase;
use function Mathias\ParserCombinator\char;

final class RunningParsersTest extends TestCase
{
    /** @test */
    public function try_throws()
    {
        $parser = char('a');
        $result = $parser->try("a");
        $this->assertSame("a", $result->output());

        $this->expectException(ParseFailure::class);
        $result = $parser->try("b");
    }

}
