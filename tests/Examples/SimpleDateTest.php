<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Examples;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\any;
use function Mathias\ParserCombinator\collect;
use function Mathias\ParserCombinator\digit;
use function Mathias\ParserCombinator\repeat;
use function Mathias\ParserCombinator\skipSpace;
use function Mathias\ParserCombinator\string;

final class SimpleDateTest extends ParserTestCase
{
    /** @test */
    public function simple_date_POC()
    {
        $jan = (string("January")->or(string("Jan")))->fmap(fn($v) => 1);
        $feb = (string("February")->or(string("Feb")))->fmap(fn($v) => 2);
        $mar = (string("March")->or(string("Mar")))->fmap(fn($v) => 3);
        // ... you get the gist

        $month = any($jan, $feb, $mar);
        $day = repeat(2, digit())->fmap('intval');
        $p1 = collect(
            $month,
            skipSpace(),
            $day
        );
        $this->assertParse([1, 28], $p1, "January 28");
        $this->assertParse([1, 28], $p1, "Jan 28");
        $this->assertParse([2, 28], $p1, "February 28");
        $this->assertParse([2, 28], $p1, "Feb 28");
    }

}
