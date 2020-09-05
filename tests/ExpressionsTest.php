<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;

use function Verraes\Parsica\eof;
use function Verraes\Parsica\keepFirst;


final class ExpressionsTest extends TestCase
{
    use ParserAssertions;

    /**
     * @test
     * @dataProvider examples
     */
    public function expression(string $input, string $expected)
    {
        $parser = keepFirst(expression(), eof());
        $result = $parser->tryString($input);
        $this->assertEquals($expected, (string)$result->output());
    }

    public function examples()
    {
        $examples = [
            ["1", "1"],
            ["1 + 1", "(1 + 1)"],
            ["1 * 1", "(1 * 1)"],
            ["(1 + 1) + 1", "((1 + 1) + 1)"],
            ["1 + (1 + 1)", "(1 + (1 + 1))"],
            ["1 * (1 + 1)", "(1 * (1 + 1))"],
            ["1 + (1 * 1)", "(1 + (1 * 1))"],
            ["(1 * 2) + (1 * 1)", "((1 * 2) + (1 * 1))"],
            ["1 + 2 + 3", "((1 + 2) + 3)"],
            ["1 * 2 * 3", "((1 * 2) * 3)"],
            ["1 * 2 + 3", "((1 * 2) + 3)"],
            ["1 + 2 * 3", "(1 + (2 * 3))"],
            ["4 + 5 + 2 * 3", "((4 + 5) + (2 * 3))"],
            ["4 + 5 * 2 * 3", "(4 + ((5 * 2) * 3))"],
            ["1 * 2 * 3 / 4 * 5", "((((1 * 2) * 3) / 4) * 5)"],
            ["1 / 2 / 3 * 4", "(((1 / 2) / 3) * 4)"],
            ["1 - 2 + 3", "((1 - 2) + 3)"],
            ["1 - 2 * 3", "(1 - (2 * 3))"],
            ["1 + 5 - 2 * 3 - 6", "(((1 + 5) - (2 * 3)) - 6)"],
            ["-1", "(-1)"],
            ["-1 + -2", "((-1) + (-2))"],
            ["-(-1)", "(-(-1))"],
            ["(-(-(1)))", "(-(-1))"],
            ["1 § 2", "(1 § 2)"],
            ["1 + 5 § 2 * 3 - 6", "((1 + 5) § ((2 * 3) - 6))"],

            // assuming non-assoc binary
            // "a + b + c" -> fail


            // assuming non-assoc binary
            // "a + b - c" -> fail

            // "a * -b"

//            ["1 * 2 * 3 / 4 * 5", "(((1 * 2) * 3) / (4 * 5)"],
        ];

        return array_combine(array_column($examples, 0), $examples);
    }

    /**
     * @test
     * @dataProvider unparsableExamples
     */
    public function unparsableExpression(string $input)
    {
        $parser = keepFirst(expression(), eof());
        $this->assertParseFails($input, $parser);
    }

    public function unparsableExamples()
    {
        $examples = [
            ["--1"],
            ["1 § 2 § 3"],
            ["1 § 2 * 3 § 4"],
            ["1 § 2 * 3 § 4 § 5"],
        ];
        return array_combine(array_column($examples, 0), $examples);
    }

}

