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
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\{between, char, choice, keepFirst, recursive, skipHSpace, string};
use function Verraes\Parsica\Expression\{expression, leftAssoc, operator, prefix};

final class BooleanExpressionsTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function booleanExpressions()
    {
        $token = fn(Parser $parser) => keepFirst($parser, skipHSpace());
        $parens = fn (Parser $parser): Parser =>  $token(between($token(char('(')), $token(char(')')), $parser));
        $term = fn(): Parser => $token(choice(
            string("TRUE")->map(fn($v) => new True_),
            string("FALSE")->map(fn($v) => new False_),
        ));
        $NOT = operator($token(string("NOT")), fn($v) => new Not_($v));
        $AND = operator($token(string("AND")), fn($l, $r) => new And_($l, $r));
        $OR = operator($token(string("OR")), fn($l, $r) => new Or_($l, $r));

        $expr = recursive();
        $expr->recurse(expression(
            $parens($expr)->or($term()),
            [
                prefix($NOT),
                leftAssoc($AND),
                leftAssoc($OR),
            ]
        ));


        $parser = $expr->thenEof();
        $input = "TRUE AND NOT (FALSE AND FALSE)";
        $expected =
            new And_(
                new True_(),
                new Not_(
                    new And_(
                        new False_(),
                        new False_()
                    )
                )
            );
        $this->assertParses($input, $parser, $expected);


        $parser = $expr->thenEof();
        $input = "TRUE AND NOT (FALSE OR TRUE AND FALSE)";
        $expected =
            new And_(
                new True_,
                new Not_(
                    new Or_(
                        new False_,
                        new And_(
                            new True_,
                            new False_
                        )
                    )
                )
            );
        $this->assertParses($input, $parser, $expected);

        // Now swapping precedence of AND and OR
        $expr = recursive();
        $expr->recurse(expression(
            $parens($expr)->or($term()),
            [
                prefix($NOT),
                leftAssoc($OR),
                leftAssoc($AND),
            ]
        ));

        $parser = $expr->thenEof();
        $input = "TRUE AND NOT (FALSE OR TRUE AND FALSE)";
        $expected =
            new And_(
                new True_,
                new Not_(
                    new And_(
                        new Or_(
                            new False_,
                            new True_
                        ),
                        new False_
                    )
                )
            );
        $this->assertParses($input, $parser, $expected);
    }
}


interface Boolean_ {}
class True_ implements Boolean_ {}
class False_ implements Boolean_ {}
class Not_ implements Boolean_ {
    private Boolean_ $boolean;
    function __construct(Boolean_ $boolean){$this->boolean = $boolean;}
}
class And_ implements Boolean_ {
    private Boolean_ $l, $r;
    function __construct(Boolean_ $l, Boolean_ $r){
        $this->l = $l;
        $this->r = $r;
    }
}
class Or_ implements Boolean_ {
    private Boolean_ $l, $r;
    function __construct(Boolean_ $l, Boolean_ $r){
        $this->l = $l;
        $this->r = $r;
    }
}