<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\{anything,
    eof,
    everything,
    fail,
    nothing,
    satisfy,
    skipWhile,
    skipWhile1,
    succeed,
    takeWhile,
    takeWhile1};
use function Parsica\Parsica\{isEqual, notPred};


final class primitivesTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function satisfy()
    {
        $parser = satisfy(isEqual('x'));
        $this->assertParses("xyz", $parser, "x");
        $this->assertRemainder("xyz", $parser, "yz");
        $this->assertParseFails("yz", $parser, "satisfy(predicate)");
        $this->assertParseFails("", $parser, "satisfy(predicate)");
    }

    /** @test */
    public function anything_()
    {
        $this->assertParses("xyz", anything(), "x");
        $this->assertRemainder("xyz", anything(), "yz");
        $this->assertParses(":-)", anything(), ":");
        $this->assertRemainder(":-)", anything(), "-)");
        $this->assertParseFails("", anything(), "anything");
    }

    /** @test */
    public function nothing()
    {
        $this->assertRemainder("xyz", nothing(), "xyz");
        $this->assertRemainder(":-)", nothing(), ":-)");
    }

    /** @test */
    public function everything()
    {
        $this->assertParses("xyz", everything(), "xyz");
        $this->assertRemainder("xyz", everything(), "");
        $this->assertParses(":-)", everything(), ":-)");
        $this->assertRemainder(":-)", everything(), "");
        $this->assertParses("", everything(), "");
    }

    /** @test */
    public function eof()
    {
        $this->assertParses("", eof(), "");
        $this->assertParseFails("xyz", eof(), "<EOF>");
    }

    /** @test */
    public function takeWhile()
    {
        $parser = takeWhile(isEqual('a'));
        $this->assertParses("xyz", $parser, "");
        $this->assertParses("xaaa", $parser, "");
        $this->assertParses("axyz", $parser, "a");
        $this->assertParses("aaaxyz", $parser, "aaa");
        $this->assertParses("aaa", $parser, "aaa");
    }

    /** @test */
    public function takeWhile_using_not()
    {
        $parser = takeWhile(notPred(isEqual('a')));

        $this->assertParses("xyza", $parser, "xyz");
        $this->assertParses("xyz", $parser, "xyz");
        $this->assertParses("xaaa", $parser, "x");
        $this->assertParses("axyz", $parser, "");
        $this->assertParses("aaaxyz", $parser, "");
        $this->assertParses("aaa", $parser, "");
    }

    /** @test */
    public function takeWile_succeeds_on_EOF()
    {
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
        $this->assertParseFails("xyz", $parser, "takeWhile1(predicate)");
        $this->assertParseFails("takeWhile1(predicate)", $parser);
        $this->assertParses("axyz", $parser, "a");
        $this->assertParses("aaaxyz", $parser, "aaa");
        $this->assertParses("aaa", $parser, "aaa");
        $this->assertParseFails("", $parser, "takeWhile1(predicate)");
    }

    /** @test */
    public function success_and_failure()
    {
        $this->assertParses("doesn't matter what we put in here", succeed(), null);
        $this->assertRemainder("no input is consumed", succeed(), "no input is consumed");
        $this->assertParseFails("doesn't matter what we put in here", fail("reason for failure"));

        $or = fail("")->or(succeed());
        $this->assertParses("failure or success is success", $or, null);
    }

    /** @test */
    public function skipWhile()
    {
        $parser = skipWhile(isEqual('a'));

        $this->assertParses("xyz", $parser, null);
        $this->assertRemainder("xyz", $parser, "xyz");
        $this->assertParses("xaaa", $parser, null);
        $this->assertRemainder("xaaa", $parser, "xaaa");
        $this->assertParses("axyz", $parser, null);
        $this->assertRemainder("axyz", $parser, "xyz");
        $this->assertParses("aaaxyz", $parser, null);
        $this->assertRemainder("aaaxyz", $parser, "xyz");
        $this->assertParses("aaa", $parser, null);
        $this->assertRemainder("aaa", $parser, "");
    }

    /** @test */
    public function skipWhile_using_not()
    {
        $parser = skipWhile(notPred(isEqual('a')));

        $this->assertParses("xyz", $parser, null);
        $this->assertRemainder("xyz", $parser, "");
        $this->assertParses("xaaa", $parser, null);
        $this->assertRemainder("xaaa", $parser, "aaa");
        $this->assertParses("axyz", $parser, null);
        $this->assertRemainder("axyz", $parser, "axyz");
        $this->assertParses("aaaxyz", $parser, null);
        $this->assertRemainder("aaaxyz", $parser, "aaaxyz");
        $this->assertParses("aaa", $parser, null);
        $this->assertRemainder("aaa", $parser, "aaa");
    }


    /** @test */
    public function skipWhile1()
    {
        $parser = skipWhile1(isEqual('a'));

        $this->assertFailOnEOF($parser);
        $this->assertParseFails("xyz", $parser);
        $this->assertParses("axyz", $parser, null);
        $this->assertRemainder("axyz", $parser, "xyz");
        $this->assertParses("aaaxyz", $parser, null);
        $this->assertRemainder("aaaxyz", $parser, "xyz");
        $this->assertParses("aaa", $parser, null);
        $this->assertRemainder("aaa", $parser, "");
        $this->assertParseFails("", $parser);
    }
}
