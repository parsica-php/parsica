<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Mathias\ParserCombinator\ParseResult\fail;
use function Mathias\ParserCombinator\ParseResult\succeed;

final class FunctorTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function map_over_ParseSuccess()
    {
        $succeed = succeed("parsed", "remainder");
        $expected = succeed("PARSED", "remainder");
        $this->assertEquals($expected, $succeed->map('strtoupper'));
    }

    /** @test */
    public function map_over_ParseFailure()
    {
        $fail = fail("expected", "got");
        $expected = fail("expected", "got");
        $this->assertEquals($expected, $fail->map('strtoupper'));
    }

}
