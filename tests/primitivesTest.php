<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\{anything,
    eof,
    everything,
    failure,
    nothing,
    satisfy,
    skipWhile,
    skipWhile1,
    success,
    takeWhile,
    takeWhile1
};
use function Verraes\Parsica\{alphaNumChar, isEqual, keepFirst, notFollowedBy, notPred, string};


final class primitivesTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function satisfy()
    {
        $parser = satisfy(isEqual('x'));
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
        $parser = takeWhile(isEqual('a'));
        $this->assertParse("", $parser, "xyz");
        $this->assertParse("", $parser, "xaaa");
        $this->assertParse("a", $parser, "axyz");
        $this->assertParse("aaa", $parser, "aaaxyz");
        $this->assertParse("aaa", $parser, "aaa");
    }

    /** @test */
    public function takeWhile_using_not()
    {
        $parser = takeWhile(notPred(isEqual('a')));

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
        $parser = takeWhile(isEqual('a'));
        $this->assertSucceedOnEOF($parser);

        $parser = takeWhile(notPred(isEqual('a')));
        $this->assertSucceedOnEOF($parser);
    }

    /** @test */
    public function takeWhile1()
    {
        $parser = takeWhile1(isEqual('a'));
        $this->assertFailOnEOF($parser);
        $this->assertNotParse($parser, "xyz", "takeWhile1(predicate)");
        $this->assertNotParse($parser, "takeWhile1(predicate)");
        $this->assertParse("a", $parser, "axyz");
        $this->assertParse("aaa", $parser, "aaaxyz");
        $this->assertParse("aaa", $parser, "aaa");
        $this->assertNotParse($parser, "", "takeWhile1(predicate)");
    }

    /** @test */
    public function success_and_failure()
    {
        $this->assertParse("", success(), "doesn't matter what we put in here");
        $this->assertRemain("no input is consumed", success(), "no input is consumed");
        $this->assertNotParse(failure(), "doesn't matter what we put in here");

        $or = failure()->or(success());
        $this->assertParse("", $or, "failure or success is success");
    }

    /** @test */
    public function skipWhile()
    {
        $parser = skipWhile(isEqual('a'));

        $this->assertParse("", $parser, "xyz");
        $this->assertRemain("xyz", $parser, "xyz");
        $this->assertParse("", $parser, "xaaa");
        $this->assertRemain("xaaa", $parser, "xaaa");
        $this->assertParse("", $parser, "axyz");
        $this->assertRemain("xyz", $parser, "axyz");
        $this->assertParse("", $parser, "aaaxyz");
        $this->assertRemain("xyz", $parser, "aaaxyz");
        $this->assertParse("", $parser, "aaa");
        $this->assertRemain("", $parser, "aaa");
    }

    /** @test */
    public function skipWhile_using_not()
    {
        $parser = skipWhile(notPred(isEqual('a')));

        $this->assertParse("", $parser, "xyz");
        $this->assertRemain("", $parser, "xyz");
        $this->assertParse("", $parser, "xaaa");
        $this->assertRemain("aaa", $parser, "xaaa");
        $this->assertParse("", $parser, "axyz");
        $this->assertRemain("axyz", $parser, "axyz");
        $this->assertParse("", $parser, "aaaxyz");
        $this->assertRemain("aaaxyz", $parser, "aaaxyz");
        $this->assertParse("", $parser, "aaa");
        $this->assertRemain("aaa", $parser, "aaa");
    }


    /** @test */
    public function skipWhile1()
    {
        $parser = skipWhile1(isEqual('a'));

        $this->assertFailOnEOF($parser);
        $this->assertNotParse($parser, "xyz");
        $this->assertParse("", $parser, "axyz");
        $this->assertRemain("xyz", $parser, "axyz");
        $this->assertParse("", $parser, "aaaxyz");
        $this->assertRemain("xyz", $parser, "aaaxyz");
        $this->assertParse("", $parser, "aaa");
        $this->assertRemain("", $parser, "aaa");
        $this->assertNotParse($parser, "");
    }

    /** @test */
    public function notFollowedBy()
    {
        $print = string("print");
        $this->assertParse("print", $print, "print('Hello World');");
        // This also outputs "print", but it wasn't our intention, because "printXYZ" is not a valid keyword:
        $this->assertParse("print", $print, "printXYZ('Hello World');");

        // with notFollowedBy:
        $print = keepFirst(string("print"), notFollowedBy(alphaNumChar()));
        $this->assertParse("print", $print, "print('Hello World');");
        $this->assertNotParse($print, "printXYZ('Hello World');");
    }

    /** @test */
    public function notFollowedBy_fluent()
    {
        $print = string("print")->notFollowedBy(alphaNumChar());
        $this->assertParse("print", $print, "print('Hello World');");
        $this->assertNotParse($print, "printXYZ('Hello World');");
    }
}
