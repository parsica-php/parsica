<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\Expression;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Expression\{LeftAssoc, NonAssoc, Operator, Postfix, Prefix, RightAssoc};
use Parsica\Parsica\Parser;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\digitChar;
use function Parsica\Parsica\eof;
use function Parsica\Parsica\Expression\binaryOperator;
use function Parsica\Parsica\Expression\expression;
use function Parsica\Parsica\Expression\leftAssoc;
use function Parsica\Parsica\Expression\nonAssoc;
use function Parsica\Parsica\Expression\operator;
use function Parsica\Parsica\Expression\postfix;
use function Parsica\Parsica\Expression\prefix;
use function Parsica\Parsica\Expression\rightAssoc;
use function Parsica\Parsica\Expression\unaryOperator;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\recursive;
use function Parsica\Parsica\skipHSpace;
use function Parsica\Parsica\string;


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
                prefix(
                    unaryOperator(char('-'), fn($v) => "(-$v)"),
                    unaryOperator(char('+'), fn($v) => "(+$v)"),
                ),
                postfix(
                    unaryOperator($token(string('--')), fn($v) => "($v--)"),
                    unaryOperator($token(string('++')), fn($v) => "($v++)"),
                ),
                leftAssoc(
                    binaryOperator($token(char('*')), fn($l, $r) => "($l * $r)"),
                    binaryOperator($token(char('/')), fn($l, $r) => "($l / $r)"),

                ),
                rightAssoc(
                    // imaginary right associative operator
                    binaryOperator($token(char('R')), fn($l, $r) => "($l R $r)"),
                    binaryOperator($token(string('R2')), fn($l, $r) => "($l R2 $r)"),
                ),
                leftAssoc(
                    binaryOperator($token(char('-')), fn($l, $r) => "($l - $r)"),
                    binaryOperator($token(char('+')), fn($l, $r) => "($l + $r)"),
                ),
                nonAssoc(
                    // imaginary non-associative operator
                    binaryOperator($token(char('§')), fn($l, $r) => "($l § $r)"),
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

