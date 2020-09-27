<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Expression;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Expression\{LeftBinary, NonAssocBinary, Operator, PostfixUnary, PrefixUnary, RightBinary};
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\eof;
use function Verraes\Parsica\Expression\expression;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\recursive;
use function Verraes\Parsica\skipHSpace;
use function Verraes\Parsica\string;


final class ExpressionsTest extends TestCase
{
    use ParserAssertions;

    private Parser $expression;

    protected function setUp() : void
    {
        /** Consumes whitespace */
        $token = fn(Parser $parser) => keepFirst($parser, skipHSpace());
        $parens = fn (Parser $parser): Parser =>  $token(between($token(char('(')), $token(char(')')), $parser));
        $term = fn(): Parser => $token(atLeastOne(digitChar()));

        $expr = recursive();
        $primaryTermParser = $parens($expr)->or($term());

        $expr->recurse(expression(
            $primaryTermParser,
            [
                new PrefixUnary(
                    new Operator(char('-'), fn($v) => "(-$v)"),
                    new Operator(char('+'), fn($v) => "(+$v)"),
                ),
                new PostfixUnary(
                    new Operator($token(string('--')), fn($v) => "($v--)"),
                    new Operator($token(string('++')), fn($v) => "($v++)"),
                ),
                new LeftBinary(
                    new Operator($token(char('*')), fn($l, $r) => "($l * $r)"),
                    new Operator($token(char('/')), fn($l, $r) => "($l / $r)"),

                ),
                new RightBinary(
                    // imaginary right associative operator
                    new Operator($token(char('R')), fn($l, $r) => "($l R $r)"),
                    new Operator($token(string('R2')), fn($l, $r) => "($l R2 $r)"),
                ),
                new LeftBinary(
                    new Operator($token(char('-')), fn($l, $r) => "($l - $r)"),
                    new Operator($token(char('+')), fn($l, $r) => "($l + $r)"),
                ),
                new NonAssocBinary(
                    // imaginary non-associative operator
                    new Operator($token(char('§')), fn($l, $r) => "($l § $r)"),
                )
            ]
        ));
        $this->expression = $expr;
    }




    /**
     * @test
     * @dataProvider examples
     */
    public function expression(string $input, string $expected)
    {
        $parser = $this->expression->thenEof();
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
            ["-(-(1))", "(-(-1))"],
            // @todo crazy slow for some reason
            // ["(-(-(1)))", "(-(-1))"],
            ["-1 * +1", "((-1) * (+1))"],
            ["1 § 2", "(1 § 2)"],
            ["1 + 5 § 2 * 3 - 6", "((1 + 5) § ((2 * 3) - 6))"],
            ["1 R 2 R 3", "(1 R (2 R 3))"],
            ["1 R 2 R 3 R 4", "(1 R (2 R (3 R 4)))"],
            ["1 - 2 * 3 R 4", "(1 - ((2 * 3) R 4))"],
            ["1 - 2 * 3 R 4 R 5", "(1 - ((2 * 3) R (4 R 5)))"],
            ["1++", "(1++)"],
            ["1++ + 2++", "((1++) + (2++))"],
            ["1--", "(1--)"],
            ["1-- + 2--", "((1--) + (2--))"],
            ["1++ + 2--", "((1++) + (2--))"],
            ["1-- + 2++", "((1--) + (2++))"],
        ];

        return array_combine(array_column($examples, 0), $examples);
    }

    /**
     * @test
     * @dataProvider unparsableExamples
     */
    public function unparsableExpressions(string $input)
    {
        $parser = $this->expression->thenEof();
        $this->assertParseFails($input, $parser);
    }

    public function unparsableExamples()
    {
        $examples = [
            ["--1"],
            ["1--++"],
            ["1++--"],
            ["1 § 2 § 3"],
            ["1 § 2 * 3 § 4"],
            ["1 § 2 * 3 § 4 § 5"],
        ];
        return array_combine(array_column($examples, 0), $examples);
    }

}

