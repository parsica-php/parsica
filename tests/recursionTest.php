<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica;

use Exception;
use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use Parsica\Parsica\StringStream;
use function Parsica\Parsica\{between, char, collect, digitChar, recursive};

final class recursionTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function recursion_on_nested_structures()
    {
        $opening = char('[');
        $closing = char(']');
        $comma = char(',');
        $digit = digitChar()->map('intval');

        $pair = recursive();
        $pair->recurse(
            between(
                $opening, $closing, collect(
                    $digit->or($pair)->thenIgnore($comma),
                    $digit->or($pair)
                )
            )
        );

        $input = "[1,2]";
        $this->assertParses($input, $pair, [1, 2]);

        $input = "[1,[2,[3,4]]]";
        $this->assertParses($input, $pair, [1, [2, [3, 4]]]);

        $input = "[[[4,3],2],1]";
        $this->assertParses($input, $pair, [[[4, 3], 2], 1]);
    }

    /** @test */
    public function nesting_multiple_recursive_parsers()
    {
        $openingSquare = char('[');
        $closingSquare = char(']');
        $openingCurly = char('{');
        $closingCurly = char('}');
        $comma = char(',');
        $digit = digitChar()->map('intval');

        $curlyPair = recursive();
        $squarePair = recursive();
        $anyPair = $curlyPair->or($squarePair);

        $expr = $digit->or($anyPair);
        $inner = collect($expr->thenIgnore($comma), $expr);

        $curlyPair->recurse(
            between($openingCurly, $closingCurly, $inner)
        );

        $squarePair->recurse(
            between($openingSquare, $closingSquare, $inner)
        );

        $input = "[1,{2,[{3,4},{5,6}]}]";
        $this->assertParses($input, $anyPair, [1, [2, [[3, 4], [5, 6]]]]);
    }

    /** @test */
    public function throw_on_multiple_calls_to_recurse()
    {
        $parser = recursive();
        $parser->recurse(char('a'));

        $this->expectException(Exception::class);
        $parser->recurse(char('b'));
    }

    /** @test */
    public function throw_when_recursing_non_recursive_parsers()
    {
        $parser = char('a');
        $this->expectException(Exception::class);
        $parser->recurse(char('b'));
    }

    /** @test */
    public function throw_for_nested_recursive_parsers_that_arent_completely_setup()
    {
        $p1 = recursive();
        $p2 = recursive();
        $p1->recurse($p2);
        $this->expectException(Exception::class);
        $p1->run(new StringStream("test"));
    }

    /** @test */
    public function using_a_recursive_parser_like_a_regular_one_after_it_was_setup()
    {
        $parser = recursive();
        $parser->recurse(char('a'));
        $labeledParser = $parser->label("test");
        $this->assertParses("abc", $labeledParser, "a");
        $this->assertParseFails("bc", $labeledParser, "test");
    }

    /** @test */
    public function calling_combinators_on_a_recursive_parser_before_it_is_setup()
    {
        $p1 = recursive();
        $p2 = char('a')->followedBy($p1->label("test"));
        $this->expectException(Exception::class);
        $this->assertParses("abc", $p2, "a");
    }
}
