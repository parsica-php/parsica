<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\ParseResult\fail;
use function Mathias\ParserCombinator\ParseResult\succeed;

final class FunctorTest extends ParserTestCase
{
    /** @test */
    public function fmap_over_ParseSuccess()
    {
        $succeed = succeed("parsed", "remaining");
        $expected = succeed("PARSED", "remaining");
        $this->assertEquals($expected, $succeed->fmap('strtoupper'));
    }

    /** @test */
    public function fmap_over_ParseFailure()
    {
        $fail = fail("expected", "got");
        $expected = fail("expected", "got");
        $this->assertEquals($expected, $fail->fmap('strtoupper'));
    }

}
