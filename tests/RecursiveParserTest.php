<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{char, collect, digit, ignore, nothing};

final class RecursiveParserTest extends ParserTestCase
{
    /** @test */
    public function recursion()
    {
        $input = "[1,2]";
        $this->assertParse([1, 2], list_(), $input);

        $input = "[1,[2,[3,4]]]";
        $this->assertParse([1, [2, [3, 4]]], list_(), $input);
    }
}

function list_(): Parser
{
    $opening = char('[')->ignore();
    $closing = char(']')->ignore();
    $comma = char(',')->ignore();
    $digit = digit()->fmap('intval');

    $list = nothing();
    $list->mutate(collect(
        $opening,
        $digit->or($list),
        $comma,
        $digit->or($list),
        $closing,
    ));
    return $list;
}

