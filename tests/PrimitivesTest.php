<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTest;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\equals;
use function Mathias\ParserCombinator\satisfy;
use function Mathias\ParserCombinator\single;

final class PrimitivesTest extends ParserTest
{
    /** @test */
    public function satisfy()
    {
        $parser = satisfy(equals('x'),);
        $this->assertParse("x", $parser, "xyz");
        $this->assertRemain("yz", $parser, "xyz");
        $this->assertNotParse($parser, "yz", "satisfy(predicate)");
        $this->assertNotParse($parser, "");
    }

    /** @test */
    public function single()
    {
        $this->assertParse("x", single(), "xyz");
        $this->assertParse(":", single(), ":-)");
        $this->assertNotParse(single(), "", "single");
    }

    /** @test */
    public function char()
    {
        $this->assertParse("a", char('a'), "abc");
        $this->assertRemain("bc", char('a'), "abc");
        $this->assertNotParse(char('a'), "bc", "char(a)");
    }


}

