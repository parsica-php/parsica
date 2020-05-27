<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\eof;
use function Mathias\ParserCombinator\equals;
use function Mathias\ParserCombinator\satisfy;
use function Mathias\ParserCombinator\single;

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
    public function single()
    {
        $this->assertParse("x", single(), "xyz");
        $this->assertParse(":", single(), ":-)");
        $this->assertNotParse(single(), "", "single");
    }

    /** @test */
    public function eof()
    {
        $this->assertParse("", eof(), "");
        $this->assertNotParse(eof(), "xyz", "eof");
    }


}

