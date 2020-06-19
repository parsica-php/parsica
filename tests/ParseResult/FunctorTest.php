<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\Internal\Fail;
use Mathias\ParserCombinator\Internal\Succeed;
use Mathias\ParserCombinator\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;

final class FunctorTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function map_over_ParseSuccess()
    {
        $succeed = new Succeed("parsed", "remainder");
        $expected = new Succeed("PARSED", "remainder");
        $this->assertEquals($expected, $succeed->map('strtoupper'));
    }

    /** @test */
    public function map_over_ParseFailure()
    {
        $fail = new Fail("expected", "got");
        $expected = new Fail("expected", "got");
        $this->assertEquals($expected, $fail->map('strtoupper'));
    }

}
