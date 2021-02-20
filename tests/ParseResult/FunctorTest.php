<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\ParseResult;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\Internal\Succeed;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use Parsica\Parsica\StringStream;

final class FunctorTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function map_over_ParseSuccess()
    {
        $succeed = new Succeed("parsed", new StringStream("remainder"));
        $expected = new Succeed("PARSED", new StringStream("remainder"));
        $this->assertEquals($expected, $succeed->map('strtoupper'));
    }

    /** @test */
    public function map_over_ParseFailure()
    {
        $remainder = new StringStream("");
        $fail = new Fail("expected", new StringStream("got"));
        $expected = new Fail("expected", new StringStream("got"));
        $this->assertEquals($expected, $fail->map('strtoupper'));
    }

}
