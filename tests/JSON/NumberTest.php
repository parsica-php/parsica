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
        $this->assertParse(0, JSON::integer(), "0");
        $this->assertParse(1, JSON::integer(), "1");
        $this->assertParse(-1, JSON::integer(), "-1");
        $this->assertParse(123, JSON::integer(), "123");
        $this->assertParse(-123, JSON::integer(), "-123");
        $this->assertRemain("1", JSON::integer(), "01", "The 0 should parse but not the 1");
        $this->assertNotParse(JSON::integer(), "-");
    }

    /** @test */
    public function number()
    {
        $this->assertParse(0.0, JSON::number(), "0");
        $this->assertParse(0.1, JSON::number(), "0.1");
        $this->assertParse(0.15, JSON::number(), "0.15");
        $this->assertParse(0.1, JSON::number(), "0.10");
        $this->assertParse(-0.1, JSON::number(), "-0.1");
        $this->assertParse(1.2345678, JSON::number(), "1.2345678");
        $this->assertParse(-1.2345678, JSON::number(), "-1.2345678");
        $this->assertParse(-1.23456789E+123, JSON::number(), "-1.23456789E+123");
        $this->assertParse(-1.23456789E-123, JSON::number(), "-1.23456789e-123");
        $this->assertParse(-1E-123, JSON::number(), "-1E-123");
    }


}
