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

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\isCharCode;
use function Verraes\Parsica\satisfy;
use function Verraes\Parsica\zeroOrMore;

final class JSON_WhitespaceTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function ws_empty()
    {
        $expected = null;
        $input = "";
        $parser = ws();
        $this->assertParse($expected, $parser, $input);
    }

    /** @test */
    public function ws_space()
    {
        $expected = null;
        $input = " ";
        $parser = ws();
        $this->assertParse($expected, $parser, $input);
    }

    /** @test */
    public function ws_tab()
    {
        $expected = null;
        $input = "\t";
        $parser = ws();
        $this->assertParse($expected, $parser, $input);
    }

    /** @test */
    public function ws_newline()
    {
        $expected = null;
        $input = "\n";
        $parser = ws();
        $this->assertParse($expected, $parser, $input);
    }

    /** @test */
    public function ws_carriage_return()
    {
        $expected = null;
        $input = "\r";
        $parser = ws();
        $this->assertParse($expected, $parser, $input);
    }

}

function ws(): Parser
{
    return zeroOrMore(satisfy(isCharCode([0x20, 0x0A, 0x0D, 0x09])))->voidLeft(null);
}
