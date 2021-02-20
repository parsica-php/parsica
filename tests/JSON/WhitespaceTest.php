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
use function Parsica\Parsica\JSON\ws;

final class WhitespaceTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function ws_empty()
    {
        $expected = null;
        $input = "";
        $parser = JSON::ws();
        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function ws_space()
    {
        $expected = null;
        $input = " ";
        $parser = JSON::ws();
        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function ws_tab()
    {
        $expected = null;
        $input = "\t";
        $parser = JSON::ws();
        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function ws_newline()
    {
        $expected = null;
        $input = "\n";
        $parser = JSON::ws();
        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function ws_carriage_return()
    {
        $expected = null;
        $input = "\r";
        $parser = JSON::ws();
        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function a_bunch_of_whitespace()
    {
        $expected = null;
        $input = "  \n \r \t a";
        $parser = JSON::ws();
        $this->assertParses($input, $parser, $expected);
        $this->assertRemainder($input, $parser, "a");

    }


}
