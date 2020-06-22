<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\ParseResult;

use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Succeed;
use Verraes\Parsica\PHPUnit\ParserAssertions;
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
