<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\Examples;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\{atLeastOne, between, char, digitChar, keepFirst, recursive, skipHSpace, string};
use function Parsica\Parsica\Expression\{binaryOperator,
    expression,
    leftAssoc,
    postfix,
    prefix,
    unaryOperator};

/**
 * Parse expressions and calculate the result
 */
final class CalculatorTest extends TestCase
{
    /** @test */
    public function calculator()
    {
        $token = fn(Parser $parser) => keepFirst($parser, skipHSpace());
        $parens = fn (Parser $parser): Parser =>  $token(between($token(char('(')), $token(char(')')), $parser));
        $term = fn(): Parser => $token(atLeastOne(digitChar()));

        $expr = recursive();
        $expr->recurse(expression(
            $parens($expr)->or($term()),
            [
                prefix(
                    unaryOperator(char('-'), fn($v) => -$v),
                    unaryOperator(char('+'), fn($v) => $v),
                ),
                postfix(
                    unaryOperator($token(string('--')), fn($v) => $v - 1),
                    unaryOperator($token(string('++')), fn($v) => $v + 1),
                ),
                leftAssoc(
                    binaryOperator($token(char('*')), fn($l, $r) => $l * $r),
                    binaryOperator($token(char('/')), fn($l, $r) => $l / $r),
                ),
                leftAssoc(
                    binaryOperator($token(char('+')), fn($l, $r) => $l + $r),
                    binaryOperator($token(char('-')), fn($l, $r) => $l - $r),
                ),
            ]
        ));


        $parser = $expr->thenEof();
        $result = $parser->tryString("(3 - 2) + -1 - 3 * (1 + 1) / 6");
        $this->assertEquals(-1, (string)$result->output());
    }
}
