<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{everything, not, nothing, eof, equals, satisfy, anything, takeWhile, takeWhile1};

final class PrimitivesTest extends ParserTestCase
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
        $this->assertParse("", nothing(), "xyz");
        $this->assertRemain("xyz", nothing(), "xyz");
        $this->assertParse("", nothing(), ":-)");
        $this->assertRemain(":-)", nothing(), ":-)");
        $this->assertParse("", nothing(), "");
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
        $this->assertNotParse($parser, "");
    }

    /** @test */
    public function takeWhile_using_not()
    {
        $this->fail("@TODO this test hangs and I'm too sleepy to figure it out");

        $parser = takeWhile(not(equals('b')));

        $this->assertParse("xyz", $parser, "xyza");
        $this->assertParse("x", $parser, "xaaa");
        $this->assertParse("", $parser, "axyz");
        $this->assertParse("", $parser, "aaaxyz");
        $this->assertParse("", $parser, "aaa");
        $this->assertNotParse($parser, "");
    }

    /** @test */
    public function takeWhile1()
    {
        $parser = takeWhile1(equals('a'));
        $this->assertNotParse($parser, "xyz", "takeWhile1(predicate)");
        $this->assertNotParse($parser, "takeWhile1(predicate)");
        $this->assertParse("a", $parser, "axyz");
        $this->assertParse("aaa", $parser, "aaaxyz");
        $this->assertParse("aaa", $parser, "aaa");
        $this->assertNotParse($parser, "", "takeWhile1(predicate)");
    }

}

