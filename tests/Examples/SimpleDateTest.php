<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Examples;

use Verraes\Parsica\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\any;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\repeat;
use function Verraes\Parsica\skipSpace;
use function Verraes\Parsica\string;

final class SimpleDateTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function simple_date()
    {
        $jan = (string("January")->or(string("Jan")))->map(fn($v) => 1);
        $feb = (string("February")->or(string("Feb")))->map(fn($v) => 2);
        $mar = (string("March")->or(string("Mar")))->map(fn($v) => 3);
        // ... you get the gist

        $month = any($jan, $feb, $mar);
        $day = repeat(2, digitChar())->map('intval');
        $p1 = collect(
            $month->thenIgnore(skipSpace()),
            $day
        );

        $this->assertParses("January 28", $p1, [1, 28]);
        $this->assertParses("Jan 28", $p1, [1, 28]);
        $this->assertParses("February 28", $p1, [2, 28]);
        $this->assertParses("Feb 28", $p1, [2, 28]);
    }

}
