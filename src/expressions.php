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
use function Verraes\Parsica\string;


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

    // UNARY PREFIX
    $negateOperator = char('-'); // we don't use token because we don't allow spaces between - and the term
    $negateFunction = fn($v): PrefixUnaryOp => new PrefixUnaryOp("-", $v);
    $negateAppl = pure($negateFunction)->apply($negateOperator->followedBy($primary))->label("negate expr");
    $precedence0 = $negateAppl->or($primary);

    // UNARY POSTFIX
    $incrOperator = token(string('++'));
    $incrFunction = fn($v): PostfixUnaryOp => new PostfixUnaryOp("++", $v);
    $incrAppl = pure($incrFunction)->apply(keepFirst($primary, $incrOperator));
    $precedence1 = $incrAppl->or($precedence0);


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
    $foldr = function (array $input, callable $function, $initial = null) use(&$foldr) {
        if(empty($input)) return $initial;
        $head = array_shift($input);
        return $function(
            $head,
            $foldr($input, $function, $initial)
        );
    };

    // RIGHT ASSOC BINARY
    $rOperator = token(char('R'));
    $rFunction = fn($l, $r): BinaryOp => new BinaryOp("R", $l, $r);
    $rAppl = pure(curry($rFunction))->apply(keepFirst($precedence2, $rOperator));
    $precedence3 =
        collect(
            many(choice($rAppl)),// 1 R 2 R
            $precedence2 // (1*2) or 3
        )->map(fn(array $o) => $foldr(
            $o[0],
            fn(callable $appl, $acc) => $appl($acc),
            $o[1]
        )

        );

    // LEFT ASSOC BINARY
    $plusOperator = token(char('+'));
    $plusFunction = fn($l, $r): BinaryOp => new BinaryOp("+", $l, $r);
    $minusOperator = token(char('-'));
    $minusFunction = fn($l, $r): BinaryOp => new BinaryOp("-", $l, $r);


    $plusAppl = pure(curry(flip($plusFunction)))->apply($plusOperator->followedBy($precedence3));
    $minusAppl = pure(curry(flip($minusFunction)))->apply($minusOperator->followedBy($precedence3));

    $precedence4 =
        collect(
            $precedence3,
            many(choice($plusAppl, $minusAppl))
        )->map(fn(array $o) => array_reduce(
            $o[1],
            fn($acc, callable $appl) => $appl($acc),
            $o[0]
        )

        );

    // NON ASSOC BINARY
    $weirdOperator = token(char('§'));
    $weirdFunction = fn($l, $r): BinaryOp => new BinaryOp("§", $l, $r);

    $precedence5 = choice(
        collect($precedence4, $weirdOperator, $precedence4)->map(fn(array $o) => $weirdFunction($o[0], $o[2])),
        $precedence4
    );



    $expr->recurse($precedence5);


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


class PrefixUnaryOp
{
    private string $operator;
    private  $value;

    function __construct(string $operator,  $value)
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
class PostfixUnaryOp
{
    private string $operator;
    private  $value;

    function __construct(string $operator,  $value)
    {
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return "(" . (string)$this->value . $this->operator . ")";
    }
}

class BinaryOp
{
    private string $operator;
    private  $left;
    private  $right;

    function __construct(string $operator,  $left,  $right)
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
