<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\nothing;
use function Mathias\ParserCombinator\eof;
use function Mathias\ParserCombinator\equals;
use function Mathias\ParserCombinator\satisfy;
use function Mathias\ParserCombinator\anything;

final class PrimitivesTest extends ParserTestCase
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
    public function anything_()
    {
        $this->assertParse("x", anything(), "xyz");
        $this->assertRemain("yz", anything(), "xyz");
        $this->assertParse(":", anything(), ":-)");
        $this->assertRemain("-)", anything(), ":-)");
        $this->assertNotParse(anything(), "", "anything");
    }

    /** @test */
    public function nothing()
    {
        $this->assertParse("", nothing(), "xyz");
        $this->assertRemain("xyz", nothing(), "xyz");
        $this->assertParse("", nothing(), ":-)");
        $this->assertRemain(":-)", nothing(), ":-)");
        $this->assertParse("", nothing(), "");
    }

    /** @test */
    public function eof()
    {
        $this->assertParse("", eof(), "");
        $this->assertNotParse(eof(), "xyz", "eof");
    }

}

