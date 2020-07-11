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
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\JSON\integer;
use function Verraes\Parsica\JSON\number;

final class NumberTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function integer()
    {
        $this->assertParse(0, integer(), "0");
        $this->assertParse(1, integer(), "1");
        $this->assertParse(-1, integer(), "-1");
        $this->assertParse(123, integer(), "123");
        $this->assertParse(-123, integer(), "-123");
        $this->assertRemain("1", integer(), "01", "The 0 should parse but not the 1" );
        $this->assertNotParse(integer(), "-");
    }

    /** @test */
    public function number()
    {
        $this->assertParse(0.0, number(), "0");
        $this->assertParse(0.1, number(), "0.1");
        $this->assertParse(0.15, number(), "0.15");
        $this->assertParse(0.1, number(), "0.10");
        $this->assertParse(-0.1, number(), "-0.1");
        $this->assertParse(1.2345678, number(), "1.2345678");
        $this->assertParse(-1.2345678, number(), "-1.2345678");
        $this->assertParse(-1.23456789E+123, number(), "-1.23456789E+123");
        $this->assertParse(-1.23456789E-123, number(), "-1.23456789e-123");
        $this->assertParse(-1E-123, number(), "-1E-123");
    }


}
