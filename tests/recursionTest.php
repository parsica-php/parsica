<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica;

use Exception;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\{between, char, collect, digitChar, recursive};

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
                $opening,
                collect(
                    $digit->or($pair)->thenIgnore($comma),
                    $digit->or($pair)
                ),
                $closing
            )
        );

        $input = "[1,2]";
        $this->assertParse([1, 2], $pair, $input);

        $input = "[1,[2,[3,4]]]";
        $this->assertParse([1, [2, [3, 4]]], $pair, $input);

        $input = "[[[4,3],2],1]";
        $this->assertParse([[[4, 3], 2], 1], $pair, $input);
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
            between($openingCurly, $inner, $closingCurly)
        );

        $squarePair->recurse(
            between($openingSquare, $inner, $closingSquare)
        );

        $input = "[1,{2,[{3,4},{5,6}]}]";
        $this->assertParse([1, [2, [[3, 4], [5, 6]]]], $anyPair, $input);
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
        $p1->run("test");
    }

    /** @test */
    public function using_a_recursive_parser_like_a_regular_one_after_it_was_setup()
    {
        $parser = recursive();
        $parser->recurse(char('a'));
        $labeledParser = $parser->label("test");
        $this->assertParse("a", $labeledParser, "abc");
        $this->assertNotParse($labeledParser, "bc", "test");
    }

    /** @test */
    public function calling_combinators_on_a_recursive_parser_before_it_is_setup()
    {
        $p1 = recursive();
        $p2 = char('a')->followedBy($p1->label("test"));
        $this->expectException(Exception::class);
        $this->assertParse("a", $p2, "abc");
    }
}
