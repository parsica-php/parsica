<?php declare(strict_types=1);

use Verraes\Parsica\Parser;
use function Cypress\Curry\curry;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;
use function Verraes\Parsica\choice;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\float;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\pure;
use function Verraes\Parsica\recursive;
use function Verraes\Parsica\sepBy2;
use function Verraes\Parsica\skipHSpace;
use function Verraes\Parsica\some;

/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @template T1
 * @psalm-param Parser<T1> $parser
 * @psalm-return Parser<T1>
 */
function token(Parser $parser) : Parser
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
    return token(float());
}

/**
 * @template T
 * @template TAcc
 *
 * @psalm-param list<T> $l
 * @psalm-param callable(TAcc, T):TAcc $f
 * @psalm-return TAcc
 */
function foldl1 (array $l, callable $f) {
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
function flip(callable $f) : callable {
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



    $parensOrTerm = parens($expr)->or(term());

    $multiplyOperator = token(char('*'));
    $multiplyFunction = fn($l, $r) : BinaryOp => new BinaryOp("*", $l, $r);
    $multiplyArity = "binary";
    $multiplyAssociativty = "left";

    $divisionOperator = token(char('/'));
    $divisionFunction = fn($l, $r) : BinaryOp => new BinaryOp("/", $l, $r);
    $divisionArity = "binary";
    $divisionAssociativty = "left";



    /** @psalm-var Parser<callable>  $multiplyAppl */
    $multiplyAppl = pure(curry(flip($multiplyFunction)))->apply($multiplyOperator->sequence($parensOrTerm));
    $divisionAppl = pure(curry(flip($divisionFunction)))->apply($divisionOperator->sequence($parensOrTerm));

    return collect(
        $parensOrTerm,
        some($multiplyAppl->or($divisionAppl))
    )->map(fn(array $o) =>
        array_reduce(
            $o[1],
            fn($acc, callable $appl) => $appl($acc),
            $o[0]
        )
    );

    /*
    $divisionAppl = pure($divisionFunction)->apply(keepFirst($parensOrTerm, $divisionOperator))->apply($parensOrTerm);
    $prec1 = recursive();
    $prec1->recurse(
        $multiplyAppl->or($divisionAppl)->or($prec1)


    $multiplyOperator->followedBy(
        pure(fn($l, $r) => new BinaryOp("*", $l, $r))->apply($parensOrTerm)->apply($parensOrTerm)
    )

    mOp >> pure(f) <*> $porT <*> $port



    $multimulti=
        sepBy2($multiplyOperator, $parensOrTerm)
        ->map(fn($o) => $foldl1($o, $multiplyFunction));

    */





    $multiplus = sepBy2(
        token(char('+')),
        $multiAndDiv->or($parensOrTerm)
    )->map(fn(array $o) => foldl1($o, fn($l, $r) : BinaryOp => new BinaryOp("+", $l, $r)));


    $expr->recurse(
        choice(
            $multiplus,
            $multiAndDiv,
            $parensOrTerm,
        )
    );


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

class BinaryOp
{
    private string $operator;

    /** @psalm-var Term|BinaryOp */
    private $left;

    /** @psalm-var Term|BinaryOp */
    private $right;

    /**
     * @psalm-param Term|BinaryOp $left
     * @psalm-param Term|BinaryOp $right
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
        return "(" . (string) $this->left . " " . $this->operator . " " . (string)$this->right . ")";
    }


}
