<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\JSON;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\JSON\JSON;
use Verraes\Parsica\PHPUnit\ParserAssertions;

final class NumberTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function integer()
    {
        $this->assertParses("0", JSON::integer(), 0);
        $this->assertParses("1", JSON::integer(), 1);
        $this->assertParses("-1", JSON::integer(), -1);
        $this->assertParses("123", JSON::integer(), 123);
        $this->assertParses("-123", JSON::integer(), -123);
        $this->assertRemainder("01", JSON::integer(), "1", "The 0 should parse but not the 1");
        $this->assertParseFails("-", JSON::integer());
    }

    /** @test */
    public function number()
    {
        $this->assertParses("0", JSON::number(), 0.0);
        $this->assertParses("0.1", JSON::number(), 0.1);
        $this->assertParses("0.15", JSON::number(), 0.15);
        $this->assertParses("0.10", JSON::number(), 0.1);
        $this->assertParses("-0.1", JSON::number(), -0.1);
        $this->assertParses("1.2345678", JSON::number(), 1.2345678);
        $this->assertParses("-1.2345678", JSON::number(), -1.2345678);
        $this->assertParses("-1.23456789E+123", JSON::number(), -1.23456789E+123);
        $this->assertParses("-1.23456789e-123", JSON::number(), -1.23456789E-123);
        $this->assertParses("-1E-123", JSON::number(), -1E-123);
    }


}
