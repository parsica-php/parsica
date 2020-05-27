<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTest;
use function Mathias\ParserCombinator\equals;
use function Mathias\ParserCombinator\satisfy;

final class PrimitivesTest extends ParserTest
{
    /** @test */
    public function satisfy()
    {
        $parser = satisfy(equals('x'));
        $this->assertParse("x", $parser, "xyz");
        $this->assertRemain("yz", $parser, "xyz");
        $this->assertNotParse($parser, "yz");
    }
}

