<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_3_0\Parser;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Cypress\Curry\curry;
use function Verraes\Parsica\{alphaChar,
    anything,
    atLeastOne,
    char,
    digitChar,
    keepFirst,
    keepSecond,
    pure,
    repeat,
    repeatList,
    sepBy,
    sepBy1,
    skipSpace,
    string};

final class ApplicativeTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function pure()
    {
        $parser = pure("<3");
        $this->assertParse("<3", $parser, "(╯°□°)╯");
    }

    /** @test */
    public function sequential_application()
    {
        $upper = pure(fn(string $v) => strtoupper($v));
        $hello = string('hello');

        // Parser<callable(a):b> -> Parser<a> -> Parser<b>
        $parser = $upper->apply($hello);
        $this->assertParse("HELLO", $parser, "hello");
    }

    /** @test */
    public function sequential_application_2()
    {
        $multiply = curry(fn($x, $y) => $x * $y);
        $number = digitChar()->map(fn($s) => intval($s));

        // Parser<callable(a, b):c> -> Parser<a> -> Parser<b> -> Parser<c>
        $parser = pure($multiply)->apply($number)->apply($number);
        $input = "35";
        $this->assertParse(15, $parser, $input);
    }

    /** @test */
    public function sequential_application_3()
    {
        $sort3 = curry(function($x, $y, $z) {
            $arr = [$x, $y, $z];
            sort($arr);
            return implode('', $arr);
        });

        $parser = pure($sort3)->apply(anything())->apply(anything())->apply(anything());
        $this->assertParse("357", $parser, "735");
        $this->assertParse("abc", $parser, "cba");
    }

    /** @test */
    public function keepFirst()
    {
        $parser = keepFirst(char('a'), char('b'));
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("c", $parser, "abc");
        $this->assertNotParse($parser, "ac");
    }

    /** @test */
    public function keepFirst_with_ignore()
    {
        $parser = keepFirst(char('a'), skipSpace());
        $this->assertParse("a", $parser, "a     ");
    }

    /** @test */
    public function keepSecond()
    {
        $parser = keepSecond(char('a'), char('b'));
        $this->assertParse("b", $parser, "abc");
        $this->assertRemain("c", $parser, "abc");
        $this->assertNotParse($parser, "ac");
    }

    /** @test */
    public function sepBy()
    {
        $parser = sepBy(string('||'), atLeastOne(alphaChar()));

        $input = "";
        $expected = [];
        $this->assertParse($expected, $parser, $input);

        $input = "foo";
        $expected = ["foo"];
        $this->assertParse($expected, $parser, $input);

        $input = "foo||";
        $expected = ["foo"];
        $this->assertParse($expected, $parser, $input);
        $this->assertRemain("||", $parser, $input);

        $input = "foo||bar";
        $expected = ["foo", "bar"];
        $this->assertParse($expected, $parser, $input);

        $input = "foo||bar||";
        $expected = ["foo", "bar"];
        $this->assertParse($expected, $parser, $input);
        $this->assertRemain("||", $parser, $input);

        $input = "foo||bar||baz";
        $expected = ["foo", "bar", "baz"];
        $this->assertParse($expected, $parser, $input);

        $input = "||";
        $this->assertParse([], $parser, $input, "The sepBy parser always succeed, even if it doesn't find anything");
        $this->assertRemain($input, $parser, $input);

        $input = "||bar||baz";
        $this->assertParse([], $parser, $input);
        $this->assertRemain($input, $parser, $input);

        $input = "||bar||";
        $this->assertParse([], $parser, $input);
        $this->assertRemain($input, $parser, $input);

        $input = "||bar";
        $this->assertParse([], $parser, $input);
        $this->assertRemain($input, $parser, $input);
    }


    /** @test */
    public function sepBy1()
    {
        $parser = sepBy1(string('||'), atLeastOne(alphaChar()));

        $input = "";
        $this->assertNotParse($parser, $input);

        $input = "||";
        $this->assertNotParse($parser, $input);

        $input = "||bar||baz";
        $this->assertNotParse($parser, $input);

        $input = "||bar||";
        $this->assertNotParse($parser, $input);

        $input = "||bar";
        $this->assertNotParse($parser, $input);


        $input = "foo";
        $expected = ["foo"];
        $this->assertParse($expected, $parser, $input);

        $input = "foo||";
        $expected = ["foo"];
        $this->assertParse($expected, $parser, $input);
        $this->assertRemain("||", $parser, $input);

        $input = "foo||bar";
        $expected = ["foo", "bar"];
        $this->assertParse($expected, $parser, $input);

        $input = "foo||bar||";
        $expected = ["foo", "bar"];
        $this->assertParse($expected, $parser, $input);
        $this->assertRemain("||", $parser, $input);

        $input = "foo||bar||baz";
        $expected = ["foo", "bar", "baz"];
        $this->assertParse($expected, $parser, $input);
    }


    /** @test */
    public function repeat_vs_repeatList()
    {
        $parser = repeat(5, alphaChar());
        $this->assertParse("hello", $parser, "hello");
        $parser = repeatList(5, alphaChar());
        $this->assertParse(["h", "e", "l", "l", "o"], $parser, "hello");

        $parser = repeatList(3, repeat(3, alphaChar()));
        $this->assertParse(["EUR", "USD", "GBP"], $parser, "EURUSDGBP");
    }
}

