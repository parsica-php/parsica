<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Examples;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Expression\LeftBinary;
use Verraes\Parsica\Expression\Operator;
use Verraes\Parsica\Expression\PostfixUnary;
use Verraes\Parsica\Expression\PrefixUnary;
use Verraes\Parsica\Parser;
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
        $primaryTermParser = $parens($expr)->or($term());
        $expr->recurse(expression(
            $primaryTermParser,
            [
                new PrefixUnary(
                    new Operator(char('-'), fn($v) => -$v),
                    new Operator(char('+'), fn($v) => $v),
                ),
                new PostfixUnary(
                    new Operator($token(string('--')), fn($v) => $v - 1),
                    new Operator($token(string('++')), fn($v) => $v + 1),
                ),
                new LeftBinary(
                    new Operator($token(char('*')), fn($l, $r) => $l * $r),
                    new Operator($token(char('/')), fn($l, $r) => $l / $r),
                ),
                new LeftBinary(
                    new Operator($token(char('+')), fn($l, $r) => $l + $r),
                    new Operator($token(char('-')), fn($l, $r) => $l - $r),
                ),
            ]
        ));


        $parser = $expr->thenEof();
        $result = $parser->tryString("(3 - 2) + -1 - 3 * (1 + 1) / 6");
        $this->assertEquals(-1, (string)$result->output());
    }
}
