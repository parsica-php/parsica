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
use function Verraes\Parsica\JSON\sign;

final class SignTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function no_sign()
    {
        $parser = JSON::sign();
        $input = "123";
        $expected = "+";
        $this->assertParse($expected, $parser, $input);
        $this->assertRemain("123", $parser, $input);
    }

    /** @test */
    public function plus_sign()
    {
        $parser = JSON::sign();
        $input = "+";
        $expected = "+";
        $this->assertParse($expected, $parser, $input);
    }
    /** @test */
    public function minus_sign()
    {
        $parser = JSON::sign();
        $input = "-123";
        $expected = "-";
        $this->assertParse($expected, $parser, $input);
    }


}

