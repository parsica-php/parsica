<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{char, collect, digit, recursive};

final class recursionTest extends ParserTestCase
{
    /** @test */
    public function recursion_on_nested_structures()
    {
        $opening = char('[')->ignore();
        $closing = char(']')->ignore();
        $comma = char(',')->ignore();
        $digit = digit()->fmap('intval');

        $pair = recursive();
        $pair->recurse(collect(
            $opening,
            $digit->or($pair),
            $comma,
            $digit->or($pair),
            $closing,
        ));

        $input = "[1,2]";
        $this->assertParse([1, 2], $pair, $input);

        $input = "[1,[2,[3,4]]]";
        $this->assertParse([1, [2, [3, 4]]], $pair, $input);

        $input = "[[[4,3],2],1]";
        $this->assertParse([[[4, 3], 2], 1], $pair, $input);
    }

    /** @test */
    public function throw_on_multiple_calls_to_recurse()
    {
        $parser = recursive();
        $parser->recurse(char('a'));

        $this->expectException(\Exception::class);
        $parser->recurse(char('b'));
    }

    /** @test */
    public function throw_when_recursing_non_recursive_parsers()
    {
        $parser = char('a');
        $this->expectException(\Exception::class);
        $parser->recurse(char('b'));
    }

    /** @test */
    public function nesting_multiple_recursive_parsers()
    {
        $openingSquare = char('[')->ignore();
        $closingSquare = char(']')->ignore();
        $openingCurly = char('{')->ignore();
        $closingCurly = char('}')->ignore();
        $comma = char(',')->ignore();
        $digit = digit()->fmap('intval');

        $curlyPair = recursive();
        $squarePair = recursive();

        $anyPair = $curlyPair->or($squarePair);
        $expr = $digit->or($anyPair);

        $curlyPair->recurse(collect(
            $openingCurly,
            $expr,
            $comma,
            $expr,
            $closingCurly,
        ));

        $squarePair->recurse(collect(
            $openingSquare,
            $expr,
            $comma,
            $expr,
            $closingSquare,
        ));

        $input = "[1,{2,[{3,4},{5,6}]}]";
        $this->assertParse([1, [2, [[3, 4], [5, 6]]]], $anyPair, $input);
    }


}
