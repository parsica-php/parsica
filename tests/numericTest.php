<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_4_0;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\float;

final class numericTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function float()
    {
        $parser = float();
        $this->assertParses("0", $parser, "0");
        $this->assertParses("0.1", $parser, "0.1");
        $this->assertParses("0.15", $parser, "0.15");
        $this->assertParses("0.10", $parser, "0.10");
        $this->assertParses("123.456", $parser, "123.456");
        $this->assertParses("1.2345678", $parser, "1.2345678");
        $this->assertParses("-1.2345678", $parser, "-1.2345678");
        $this->assertParses("-1.23456789E+123", $parser, "-1.23456789E+123");
        $this->assertParses("-1.23456789e-123", $parser, "-1.23456789E-123");
        $this->assertParses("-1E-123", $parser, "-1E-123");
    }

    /** @test */
    public function float_maps_correctly()
    {
        $parser = float()->map('floatval');
        $this->assertParses("123.456", $parser, 123.456);
        $this->assertParses("-0.1", $parser, -0.1);
        $this->assertParses("1.2345678", $parser, 1.2345678);
        $this->assertParses("-1.2345678", $parser, -1.2345678);
        $this->assertParses("-1.23456789E+123", $parser, -1.23456789E+123);
        $this->assertParses("-1.23456789e-123", $parser, -1.23456789E-123);
        $this->assertParses("-1E-123", $parser, -1E-123);
    }
}
