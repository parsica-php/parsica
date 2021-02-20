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
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\{alphaChar, between, char, collect, digitChar, skipHSpace1, space, string};

final class ExcelTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function spaceOrOperatorDependingOnContext()
    {
        // https://twitter.com/Mark_Baker/status/1309919606887374849?s=20
        // and https://twitter.com/Mark_Baker/status/1309960902482026498?s=20
        // `=SUM(B7:D7 C6:C8)` where space is the intersection operator for the
        // intersection between the two ranges B7:D7 and C6:C8 (ie. C7),
        // and  `=A1 & B1` where the space is simply whitespace and should be ignored

        $parser = $this->excelParser();


        $input = "=SUM(B7:D7 C6:C8)";
        $expected = new Sum(
            new Intersection(
                new Range(new Cell("B", "7"), new Cell("D", "7")),
                new Range(new Cell("C", "6"), new Cell("C", "8")),
            )
        );
        $this->assertParses($input, $parser, $expected);


        $input = "=A1 & B1";
        $expected = new Ampersand(
            new Cell("A", "1"),
            new Cell("B", "1"),
        );
        $this->assertParses($input, $parser, $expected);
    }

    private function excelParser(): Parser
    {
        $parens = fn(Parser $p): Parser => between(char('('), char(')'), $p);
        $cell = collect(alphaChar(), digitChar())
            ->map(fn($o) => new Cell($o[0], $o[1]));
        $range = collect($cell, char(':'), $cell)
            ->map(fn($o) => new Range($o[0], $o[2]));
        $intersection = collect($range, space(), $range)
            ->map(fn($o) => new Intersection($o[0], $o[2]));
        $sum = (string('=SUM')->followedBy($parens($intersection)))
            ->map(fn($o) => new Sum($o));


        // consumes space before and after Parser $p
        $token = fn(Parser $p): Parser => between(skipHSpace1(), skipHSpace1(), $p);
        $ampersand = char('=')->followedBy(collect(
            $cell,
            $token(char('&')),
            $cell
        ))->map(fn($o) => new Ampersand($o[0], $o[2]));


        return $sum->or($ampersand);
    }

}

class Cell
{
    private $col;
    private $row;

    function __construct($col, $row)
    {
        $this->col = $col;
        $this->row = $row;
    }
}
class Range
{
    private Cell $from;
    private Cell $to;

    function __construct(Cell $from, Cell $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
}
class Intersection
{
    private Range $l;
    private Range $r;

    function __construct(Range $l, Range $r)
    {
        $this->l = $l;
        $this->r = $r;
    }
}
class Sum
{
    private Intersection $intersection;

    function __construct(Intersection $intersection)
    {
        $this->intersection = $intersection;
    }
}
class Ampersand
{
    private Cell $l;
    private Cell $r;

    function __construct(Cell $l, Cell $r)
    {
        $this->l = $l;
        $this->r = $r;
    }
}
