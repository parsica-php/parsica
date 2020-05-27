<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinator\satisfy;

final class PrimitivesTest extends ParserTest
{
    /** @test */
    public function satisfy()
    {
        $predicate = fn($input) => $input == 'x';
        $parser = satisfy($predicate);
        $this->assertParse($parser, "xyz", "x");
        $this->assertRemain($parser, "xyz", "yz");
        $this->assertNotParse($parser, "yz");
    }
}

