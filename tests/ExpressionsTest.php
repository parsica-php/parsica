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
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;
use function Verraes\Parsica\choice;
use function Verraes\Parsica\eof;
use function Verraes\Parsica\float;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\recursive;
use function Verraes\Parsica\sepBy2;
use function Verraes\Parsica\skipHSpace;

function token(Parser $parser)
{
    return keepFirst($parser, skipHSpace());
}

function operator(string $op): Parser
{
    return token(char($op));
}

function parens(Parser $parser): Parser
{
    return between(token(char('(')), token(char(')')), $parser);
}


function term(): Parser
{
    return token(float());
}

function expression(): Parser
{
    $expr = recursive();

    $foldl1 = function (array $l, callable $f) {
        $head = array_shift($l);
        return array_reduce($l, $f, $head);
    };

    $pOrT = parens($expr)->or(term());

    $multimulti = sepBy2(token(char('*')), $pOrT)
        ->map(fn($o) => $foldl1($o, fn($l, $r) => new BinaryOp("*", $l, $r)));
    $multiplus = sepBy2(
        token(char('+')),
        $multimulti->or($pOrT)
    )->map(fn($o) => $foldl1($o, fn($l, $r) => new BinaryOp("+", $l, $r)));


    $expr->recurse(
        choice(
            $multiplus,
            $multimulti,
            $pOrT,
        )
    );


    return $expr;
}

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
        ];

        return array_combine(array_column($examples, 0), $examples);
    }

}

class Term
{
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }

}

class BinaryOp
{
    private $operator;
    private $left;
    private $right;

    function __construct($operator, $left, $right)
    {
        $this->operator = $operator;
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return "(" . $this->left . " " . $this->operator . " " . $this->right . ")";
    }


}
