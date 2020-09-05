<?php declare(strict_types=1);

/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Verraes\Parsica\Parser;
use function Cypress\Curry\curry;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;
use function Verraes\Parsica\choice;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\float;
use function Verraes\Parsica\integer;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\many;
use function Verraes\Parsica\map;
use function Verraes\Parsica\pure;
use function Verraes\Parsica\recursive;
use function Verraes\Parsica\skipHSpace;


/*
 * i've read the haskell implementation, and I think i got an idea of how it works
cλementd on mastodon · 4:03 PM
the main fold is there:
https://github.com/mrkkrp/megaparsec/blob/5.2.0/Text/Megaparsec/Expr.hs#L87

it builds the grammar, layer after layer, from the bottom up
then, _at each  precedence level_, it groups the operators based on their arity and associativity: https://github.com/mrkkrp/megaparsec/blob/5.2.0/Text/Megaparsec/Expr.hs#L95
it starts by trying to parse unary operators
https://github.com/mrkkrp/megaparsec/blob/5.2.0/Text/Megaparsec/Expr.hs#L96
cλementd on mastodon · 4:07 PM
then, it handles right-associative operators, then left-associative operators, then non-associative parsers, and as a last resort succeeds with only the parsed term (the `return x`): https://github.com/mrkkrp/megaparsec/blob/5.2.0/Text/Megaparsec/Expr.hs#L94
cλementd on mastodon · 4:09 PM
the left-associative parser is recursive:
https://github.com/mrkkrp/megaparsec/blob/5.2.0/Text/Megaparsec/Expr.hs#L126
it parses the operator, a term, and then optionally recurses
The non-associative parser does not recurse
https://github.com/mrkkrp/megaparsec/blob/5.2.0/Text/Megaparsec/Expr.hs#L120
the right-associative parser also recurses, but in a… right associative way
https://github.com/mrkkrp/megaparsec/blob/5.2.0/Text/Megaparsec/Expr.hs#L137
cλementd on mastodon · 4:11 PM
the important part is that each level of parser uses the parser from the level beneath (the `p` parameter in the `pInfix{N,L,R}` functions, and the `term` parameter in `pTerm`.
as i suspected, the pre/post fix parser is handled separately at each level, before parsing binary ops
cλementd on mastodon · 4:14 PM
an important point regarding foldl / foldr

The `foldr splitOp`could very well be written with foldl because slpitOp is associative. foldr is generally preferred to foldl for associative functions. The `foldl` used to layer the parsers is however important because the parser-building function is not associative
cλementd on mastodon · 4:35 PM
nvm, splitOp is not associative, but that's not a huge issue in PHP:with linked lists in haskell we want to prepend as much as possible, but with php's arrays, it's not really a problem
cλementd on mastodon · 4:42 PM
 */


/**
 * @template T1
 * @psalm-param Parser<T1> $parser
 * @psalm-return Parser<T1>
 */
function token(Parser $parser): Parser
{
    return keepFirst($parser, skipHSpace());
}

/**
 * @psalm-return Parser<string>
 */
function operator(string $op): Parser
{
    return token(char($op));
}


/**
 * @template T1
 * @psalm-param Parser<T1> $parser
 * @psalm-return Parser<T1>
 */
function parens(Parser $parser): Parser
{
    return token(between(token(char('(')), token(char(')')), $parser));
}

/**
 * @psalm-return Parser<string>
 */
function term(): Parser
{
    return token(atLeastOne(digitChar()));
}

/**
 * @template T
 * @template TAcc
 *
 * @psalm-param list<T> $l
 * @psalm-param callable(TAcc, T):TAcc $f
 * @psalm-return TAcc
 */
function foldl1(array $l, callable $f)
{
    $head = array_shift($l);
    return array_reduce($l, $f, $head);
}

/**
 * @template Ta
 * @template Tb
 * @template Tc
 * @psalm-param callable(Ta, Tb):Tc $f
 * @psalm-return callable(Tb, Ta):Tc
 */
function flip(callable $f): callable
{
    /**
     * @psalm-param Ta $x
     * @psalm-param Tb $y
     * @psalm-return Tc
     */
    return fn($x, $y) => $f($y, $x);
}


/**
 * @psalm-return Parser<BinaryOp>
 */
function expression(): Parser
{
    $expr = recursive();


    // https://en.wikipedia.org/wiki/Operator-precedence_parser
    $primary = parens($expr)->or(term());

    $negateOperator = char('-'); // we don't use token because we don't allow spaces between - and the term
    $negateFunction = fn($v): UnaryOp => new UnaryOp("-", $v);
    $negateAppl = pure($negateFunction)->apply($negateOperator->followedBy($primary))->label("negate expr");
    $precedence1 = $negateAppl->or($primary);

    $multiplyOperator = token(char('*'));
    $multiplyFunction = fn($l, $r): BinaryOp => new BinaryOp("*", $l, $r);
    $multiplyArity = "binary";
    $multiplyAssociativity = "left";

    $divisionOperator = token(char('/'));
    $divisionFunction = fn($l, $r): BinaryOp => new BinaryOp("/", $l, $r);
    $divisionArity = "binary";
    $divisionAssociativity = "left";


    /** @psalm-pyvar Parser<callable>  $multiplyAppl */
    $multiplyAppl = pure(curry(flip($multiplyFunction)))->apply($multiplyOperator->followedBy($precedence1));
    $divisionAppl = pure(curry(flip($divisionFunction)))->apply($divisionOperator->followedBy($precedence1));

    $precedence2 =
        collect(
            $precedence1,
            many(choice($multiplyAppl, $divisionAppl))
        )->map(fn(array $o) => array_reduce(
            $o[1],
            fn($acc, callable $appl) => $appl($acc),
            $o[0]
        )
        );


    $plusOperator = token(char('+'));
    $plusFunction = fn($l, $r): BinaryOp => new BinaryOp("+", $l, $r);
    $minusOperator = token(char('-'));
    $minusFunction = fn($l, $r): BinaryOp => new BinaryOp("-", $l, $r);


    $plusAppl = pure(curry(flip($plusFunction)))->apply($plusOperator->followedBy($precedence2));
    $minusAppl = pure(curry(flip($minusFunction)))->apply($minusOperator->followedBy($precedence2));

    $precendence3 =
        collect(
            $precedence2,
            many(choice($plusAppl, $minusAppl))
        )->map(fn(array $o) => array_reduce(
            $o[1],
            fn($acc, callable $appl) => $appl($acc),
            $o[0]
        )

        );


    $expr->recurse($precendence3);


    return $expr;
}


class Term
{
    private string $value;

    function __construct(string $value)
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

class UnaryOp
{
    private string $operator;
    /** @psalm-var Term|BinaryOp|UnaryOp */
    private $value;

    /**
     * @psalm-param Term|BinaryOp|UnaryOp $value
     */
    function __construct(string $operator, $value)
    {
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return "(" . $this->operator . (string)$this->value . ")";
    }
}

class BinaryOp
{
    private string $operator;

    /** @psalm-var Term|BinaryOp|UnaryOp */
    private $left;

    /** @psalm-var Term|BinaryOp|UnaryOp */
    private $right;

    /**
     * @psalm-param Term|BinaryOp|UnaryOp $left
     * @psalm-param Term|BinaryOp|UnaryOp $right
     */
    function __construct(string $operator, $left, $right)
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
        return "(" . (string)$this->left . " " . $this->operator . " " . (string)$this->right . ")";
    }


}
