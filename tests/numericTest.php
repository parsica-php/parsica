<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\float;
use function Parsica\Parsica\integer;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\skipSpace1;

final class numericTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function integer()
    {
        $parser = integer();
        $this->assertParses("0", $parser, "0");
        $this->assertParses("1", $parser, "1");
        $this->assertParses("10", $parser, "10");
        $this->assertParses("972115541", $parser, "972115541");
        $this->assertParses("-0", $parser, "-0");
        $this->assertParses("-1", $parser, "-1");
        $this->assertParses("-10", $parser, "-10");
        $this->assertParses("-972115541", $parser, "-972115541");
    }

    /** @test */
    public function integer_maps_to_int()
    {
        $parser = integer()->map('intval');
        $this->assertParses("0", $parser, 0);
        $this->assertParses("1", $parser, 1);
        $this->assertParses("10", $parser, 10);
        $this->assertParses("972115541", $parser, 972115541);
        $this->assertParses("-0", $parser, 0);
        $this->assertParses("-1", $parser, -1);
        $this->assertParses("-10", $parser, -10);
        $this->assertParses("-972115541", $parser, -972115541);
    }

    /** @test */
    public function integer_fails()
    {
        $parser = keepFirst(integer(), skipSpace1());
        $this->assertParseFails("00", $parser);
        $this->assertParseFails("01", $parser);
        $this->assertParseFails("+1", $parser);
    }

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
    public function float_fails()
    {
        $parser = keepFirst(float(), skipSpace1()); // avoid false positives
        $this->assertParseFails("00", $parser);
        $this->assertParseFails("0. 15", $parser);
        $this->assertParseFails("00.10", $parser);
        $this->assertParseFails(" + 00.10", $parser);
        $this->assertParseFails(" + 123.456", $parser);
        $this->assertParseFails("1.234.5678", $parser);
        $this->assertParseFails(" - 1,2345678", $parser);
        $this->assertParseFails("--1.234E123", $parser);
        $this->assertParseFails(" - 1.234e", $parser);
        $this->assertParseFails(" - 1E-123E - 456", $parser);
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
