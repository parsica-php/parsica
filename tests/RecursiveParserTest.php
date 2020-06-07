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
    $list = nothing();
    $list->mutate(collect(
        ignore(char('[')),
        digit()->fmap('intval')->or($list),
        ignore(char(',')),
        digit()->fmap('intval')->or($list),
        ignore(char(']'))
    ));
    return $list;
}

