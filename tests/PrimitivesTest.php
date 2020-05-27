<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinator\equals;
use function Mathias\ParserCombinator\satisfy;

final class PrimitivesTest extends ParserTest
{
    /** @test */
    public function satisfy()
    {
        $parser = satisfy(equals('x'));
        $this->assertParse($parser, "xyz", "x");
        $this->assertRemain($parser, "xyz", "yz");
        $this->assertNotParse($parser, "yz");
    }
}

