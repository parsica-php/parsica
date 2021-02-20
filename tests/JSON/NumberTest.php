<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\JSON;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\JSON\JSON;
use Parsica\Parsica\PHPUnit\ParserAssertions;

final class NumberTest extends TestCase
{
    use ParserAssertions;

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
        $this->assertParses("-1E-123          ", JSON::number(), -1E-123);
    }


}
