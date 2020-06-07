<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{anything, eof, equals, everything, not, nothing, satisfy, takeWhile, takeWhile1};

final class primitivesTest extends ParserTestCase
{
    /** @test */
    public function satisfy()
    {
        $parser = satisfy(equals('x'));
        $this->assertParse("x", $parser, "xyz");
        $this->assertRemain("yz", $parser, "xyz");
        $this->assertNotParse($parser, "yz", "satisfy(predicate)");
        $this->assertNotParse($parser, "", "satisfy(predicate)");
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
        $this->assertRemain("xyz", nothing(), "xyz");
        $this->assertRemain(":-)", nothing(), ":-)");
    }

    /** @test */
    public function everything()
    {
        $this->assertParse("xyz", everything(), "xyz");
        $this->assertRemain("", everything(), "xyz");
        $this->assertParse(":-)", everything(), ":-)");
        $this->assertRemain("", everything(), ":-)");
        $this->assertParse("", everything(), "");
    }

    /** @test */
    public function eof()
    {
        $this->assertParse("", eof(), "");
        $this->assertNotParse(eof(), "xyz", "eof");
    }

    /** @test */
    public function takeWhile()
    {
        $parser = takeWhile(equals('a'));
        $this->assertParse("", $parser, "xyz");
        $this->assertParse("", $parser, "xaaa");
        $this->assertParse("a", $parser, "axyz");
        $this->assertParse("aaa", $parser, "aaaxyz");
        $this->assertParse("aaa", $parser, "aaa");
    }

    /** @test */
    public function takeWhile_using_not()
    {
        $parser = takeWhile(not(equals('a')));

        $this->assertParse("xyz", $parser, "xyza");
        $this->assertParse("xyz", $parser, "xyz");
        $this->assertParse("x", $parser, "xaaa");
        $this->assertParse("", $parser, "axyz");
        $this->assertParse("", $parser, "aaaxyz");
        $this->assertParse("", $parser, "aaa");
    }

    /** @test */
    public function not_sure_how_takeWhile_should_deal_with_EOF()
    {
        // For now let's have it succeed until we figure it out.
        $parser = takeWhile(equals('a'));
        $this->assertSucceedOnEOF($parser);

        $parser = takeWhile(not(equals('a')));
        $this->assertSucceedOnEOF($parser);
    }


    /** @test */
    public function takeWhile1()
    {
        $parser = takeWhile1(equals('a'));
        $this->assertFailOnEOF($parser);
        $this->assertNotParse($parser, "xyz", "takeWhile1(predicate)");
        $this->assertNotParse($parser, "takeWhile1(predicate)");
        $this->assertParse("a", $parser, "axyz");
        $this->assertParse("aaa", $parser, "aaaxyz");
        $this->assertParse("aaa", $parser, "aaa");
        $this->assertNotParse($parser, "", "takeWhile1(predicate)");
    }
}
