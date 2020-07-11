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
use function Verraes\Parsica\char;
use function Verraes\Parsica\nothing;

final class JSON_SignTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function no_sign()
    {
        $parser = sign();
        $input = "123";
        $expected = "+";
        $this->assertParse($expected, $parser, $input);
        $this->assertRemain("123", $parser, $input);
    }

    /** @test */
    public function plus_sign()
    {
        $parser = sign();
        $input = "+123";
        $expected = "+";
        $this->assertParse($expected, $parser, $input);
    }
    /** @test */
    public function minus_sign()
    {
        $parser = sign();
        $input = "-123";
        $expected = "-";
        $this->assertParse($expected, $parser, $input);
    }


}


function sign(): Parser{
    return char('+')->or(char('-'))->or(nothing()->voidLeft("+"));
}
