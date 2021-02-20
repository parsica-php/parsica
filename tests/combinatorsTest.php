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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\{alphaChar,
    alphaNumChar,
    any,
    anySingle,
    anySingleBut,
    atLeastOne,
    between,
    char,
    choice,
    collect,
    digitChar,
    either,
    float,
    identity,
    keepFirst,
    lookAhead,
    many,
    noneOf,
    noneOfS,
    notFollowedBy,
    oneOf,
    oneOfS,
    optional,
    punctuationChar,
    repeat,
    repeatList,
    sepBy,
    sepBy1,
    sequence,
    skipSpace,
    string,
    stringI,
    takeRest,
    whitespace};

final class combinatorsTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function identity()
    {
        $parser = identity(char('a'));
        $this->assertParses("abc", $parser, "a");
        $this->assertRemainder("abc", $parser, "bc");
    }

    /** @test */
    public function identity_does_not_show_up_in_error_messages()
    {
        $parser = identity(char('a'));
        $this->assertParseFails("bc", $parser, "'a'");
    }

    /** @test */
    public function thenIgnore()
    {
        $parser = string('abcd')
            ->thenIgnore(char('-'))
            ->append(string('efgh'));
        $this->assertParses("abcd-efgh", $parser, "abcdefgh");
        $this->assertParseFails("abcdefgh", $parser);

        // smae with optional dash
        $parser = string('abcd')
            ->thenIgnore(optional(char('-')))
            ->append(string('efgh'));
        $this->assertParses("abcdefgh", $parser, "abcdefgh");
        $this->assertParses("abcd-efgh", $parser, "abcdefgh");
    }


    /** @test */
    public function anySingle()
    {
        $parser = anySingle();
        $this->assertFailOnEOF($parser);
        $this->assertParses("a", $parser, "a");
        $this->assertParses("abc", $parser, "a");
        $this->assertParses(":", $parser, ":");
        $this->assertParses(":-)", $parser, ":");
    }

    /** @test */
    public function anySingleBut()
    {
        $parser = anySingleBut("x");
        $this->assertFailOnEOF($parser);
        $this->assertParses("a", $parser, "a");
        $this->assertRemainder("a", $parser, "");
        $this->assertParses("abc", $parser, "a");
        $this->assertRemainder("abc", $parser, "bc");
        $this->assertParseFails("x", $parser);
        $this->assertParseFails("xxx", $parser);
    }

    /** @test */
    public function oneOf()
    {
        $parser = oneOf(['a', 'b', 'c']);
        $this->assertFailOnEOF($parser);
        $this->assertParses("a", $parser, "a");
        $this->assertParses("ax", $parser, "a");
        $this->assertParses("b", $parser, "b");
        $this->assertParses("c", $parser, "c");
        $this->assertParseFails("xyz", $parser);
    }

    /** @test */
    public function oneOf_expects_single_chars()
    {
        $this->expectException(InvalidArgumentException::class);
        $parser = oneOf(['a', "long", "c"]);
    }

    /** @test */
    public function oneOfS()
    {
        $parser = oneOfS("abc");
        $this->assertParses("ax", $parser, "a");
        $this->assertParseFails("xyz", $parser);
    }

    /** @test */
    public function noneOf()
    {
        $parser = noneOf(['a', 'b', 'c']);
        $this->assertFailOnEOF($parser);
        $this->assertParseFails("a", $parser);
        $this->assertParseFails("ax", $parser);
        $this->assertParseFails("b", $parser);
        $this->assertParses("xyz", $parser, "x");
        $this->assertRemainder("xyz", $parser, "yz");
    }

    /** @test */
    public function noneOfS()
    {
        $parser = noneOfS("abc");
        $this->assertParseFails("ax", $parser);
        $this->assertParses("xyz", $parser, "x");
    }

    /** @test */
    public function takeRest()
    {
        $parser = takeRest();
        $this->assertSucceedOnEOF($parser);
        $this->assertParses("xyz", $parser, "xyz");
        $this->assertRemainder("xyz", $parser, "");
    }


    /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));
        $this->assertFailOnEOF($parser);
        $this->assertParses("abc", $parser, "a");
        $this->assertRemainder("abc", $parser, "bc");
        $this->assertParses("bc", $parser, "b");
        $this->assertRemainder("bc", $parser, "c");
        $this->assertParseFails("cd", $parser);
    }

    /** @test */
    public function either_with_mixed_type()
    {
        $parser = either(
            atLeastOne(digitChar())->map(fn(string $o)=> intval($o))->thenEof(),
            atLeastOne(alphaNumChar())->thenEof(),
        );

        $actual = $parser->tryString("123")->output();
        $this->assertIsInt($actual);
        $this->assertEquals("123", $actual);

        $actual = $parser->tryString("123a")->output();
        $this->assertIsString($actual);
        $this->assertEquals("123a", $actual);

    }

    /** @test */
    public function sequence()
    {
        $parser = sequence(char('a'), char('b'));
        $this->assertFailOnEOF($parser);
        $this->assertParses("abc", $parser, "b");
        $this->assertRemainder("abc", $parser, "c");
        $this->assertParseFails("acc", $parser);
        $this->assertParseFails("cab", $parser);
    }

    /** @test */
    public function collect()
    {
        $parser =
            collect(
                string("Hello")
                    ->append(skipSpace())->thenIgnore(char(','))
                    ->append(skipSpace()),
                string("world")
                    ->thenIgnore(char('!'))
            );

        $expected = ["Hello", "world"];
        $this->assertFailOnEOF($parser);
        $this->assertParses("Hello , world!", $parser, $expected);
        $this->assertParses("Hello,world!", $parser, $expected);
    }

    /** @test */
    public function collectFails()
    {
        $parser =
            collect(
                string("Hello"),
                string("World")
            );
        $this->assertFailOnEOF($parser);
        $this->assertParseFails("HiWorld", $parser, "'Hello'");
        $this->assertParseFails("HelloPlanet", $parser, "'World'");
    }

    /**
     * @test
     */
    public function atLeastOne()
    {
        $parser = atLeastOne(char('a'));
        $this->assertFailOnEOF($parser);
        $this->assertParses("a", $parser, "a");
        $this->assertParses("aa", $parser, "aa");
        $this->assertParses("aaaaa", $parser, "aaaaa");
        $this->assertParses("aaabb", $parser, "aaa");
        $this->assertParseFails("bb", $parser);
    }

    /** @test */
    public function any_()
    {
        $symbol = any(string("€"), string("$"));
        $amount = float()->map('floatval');
        $money = collect($symbol, $amount);

        $this->assertFailOnEOF($money);
        $this->assertParses("€", $symbol, "€");
        $this->assertParses("15.23", $amount, 15.23);
        $this->assertParses("€15.23", $money, ["€", 15.23]);
        $this->assertParses("$15", $money, ["$", 15.0]);
        $this->assertParseFails("£12.13", $money);
    }

    /** @test */
    public function choice()
    {
        $symbol = choice(string("€"), string("$"));
        $amount = float()->map('floatval');
        $money = collect($symbol, $amount);

        $this->assertFailOnEOF($money);
        $this->assertParses("€", $symbol, "€");
        $this->assertParses("15.23", $amount, 15.23);
        $this->assertParses("€15.23", $money, ["€", 15.23]);
        $this->assertParses("$15", $money, ["$", 15.0]);
        $this->assertParseFails("£12.13", $money);
    }

    /** @test */
    public function skipMany()
    {
        //skipMany p applies the parser p zero or more times, skipping its result.
        self::markTestIncomplete();
    }


    /** @test */
    public function keepFirst__inside_a_nested_parser()
    {
        $movies = any(stringI('movie'), stringI('movies'), stringI('film'), stringI('films'))->followedBy(skipSpace());
        $number = atLeastOne(digitChar())->map('intval');
        $words = many(any(alphaChar(), punctuationChar(), whitespace()));
        $parser = $words->followedBy(keepFirst($number, skipSpace()->followedBy($movies)));

        $input = "I watched 23 MOVIES this week ";
        $this->assertParses($input, $parser, 23);
    }

    /** @test */
    public function between()
    {
        $parser = between(char('{'), char('}'), atLeastOne(alphaNumChar()));
        $input = "{foo}";
        $this->assertParses($input, $parser, "foo");
    }

    /** @test */
    public function between_failure()
    {
        $parser = between(char('{'), char('}'), atLeastOne(alphaNumChar()));
        $this->assertParseFails("foo}", $parser, "'{'");
        $this->assertParseFails("{foo", $parser, "'}'");
        $this->assertParseFails("{}", $parser, "A-Z or a-z or 0-9");
    }

    /** @test */
    public function notFollowedBy()
    {
        $print = string("print");
        $this->assertParses("print('Hello World');", $print, "print");
        // This also outputs "print", but it wasn't our intention, because "printXYZ" is not a valid keyword:
        $this->assertParses("printXYZ('Hello World');", $print, "print");

        // with notFollowedBy:
        $print = keepFirst(string("print"), notFollowedBy(alphaNumChar()));
        $this->assertParses("print('Hello World');", $print, "print");
        $this->assertParseFails("printXYZ('Hello World');", $print);
    }

    /** @test */
    public function notFollowedBy_fluent()
    {
        $print = string("print")->notFollowedBy(alphaNumChar());
        $this->assertParses("print('Hello World');", $print, "print");
        $this->assertParseFails("printXYZ('Hello World');", $print);
    }

    /** @test */
    public function lookAhead()
    {
        $parser = lookAhead(stringI("hello"));

        // On success, lookAhead succeeds without consuming input
        $this->assertParses("Hello, world!", $parser, "Hello");
        $this->assertRemainder("Hello, world!", $parser, "Hello, world!");

        // On fail, lookAhead fails without consuming input
        $this->assertParseFails("Hi, world!", $parser);
    }

    /** @test */
    public function sepBy()
    {
        $parser = sepBy(string('||'), atLeastOne(alphaChar()));

        $input = "";
        $expected = [];
        $this->assertParses($input, $parser, $expected);

        $input = "foo";
        $expected = ["foo"];
        $this->assertParses($input, $parser, $expected);

        $input = "foo||";
        $expected = ["foo"];
        $this->assertParses($input, $parser, $expected);
        $this->assertRemainder($input, $parser, "||");

        $input = "foo||bar";
        $expected = ["foo", "bar"];
        $this->assertParses($input, $parser, $expected);

        $input = "foo||bar||";
        $expected = ["foo", "bar"];
        $this->assertParses($input, $parser, $expected);
        $this->assertRemainder($input, $parser, "||");

        $input = "foo||bar||baz";
        $expected = ["foo", "bar", "baz"];
        $this->assertParses($input, $parser, $expected);

        $input = "||";
        $this->assertParses($input, $parser, [], "The sepBy parser always succeed, even if it doesn't find anything");
        $this->assertRemainder($input, $parser, $input);

        $input = "||bar||baz";
        $this->assertParses($input, $parser, []);
        $this->assertRemainder($input, $parser, $input);

        $input = "||bar||";
        $this->assertParses($input, $parser, []);
        $this->assertRemainder($input, $parser, $input);

        $input = "||bar";
        $this->assertParses($input, $parser, []);
        $this->assertRemainder($input, $parser, $input);
    }


    /** @test */
    public function sepBy1()
    {
        $parser = sepBy1(string('||'), atLeastOne(alphaChar()));

        $input = "";
        $this->assertParseFails($input, $parser, "at least one A-Z or a-z, separated by '||'");

        $input = "||";
        $this->assertParseFails($input, $parser);

        $input = "||bar||baz";
        $this->assertParseFails($input, $parser);

        $input = "||bar||";
        $this->assertParseFails($input, $parser);

        $input = "||bar";
        $this->assertParseFails($input, $parser);


        $input = "foo";
        $expected = ["foo"];
        $this->assertParses($input, $parser, $expected);

        $input = "foo||";
        $expected = ["foo"];
        $this->assertParses($input, $parser, $expected);
        $this->assertRemainder($input, $parser, "||");

        $input = "foo||bar";
        $expected = ["foo", "bar"];
        $this->assertParses($input, $parser, $expected);

        $input = "foo||bar||";
        $expected = ["foo", "bar"];
        $this->assertParses($input, $parser, $expected);
        $this->assertRemainder($input, $parser, "||");

        $input = "foo||bar||baz";
        $expected = ["foo", "bar", "baz"];
        $this->assertParses($input, $parser, $expected);
    }


    /** @test */
    public function repeat_vs_repeatList()
    {
        $parser = repeat(5, alphaChar());
        $this->assertParses("hello", $parser, "hello");
        $parser = repeatList(5, alphaChar());
        $this->assertParses("hello", $parser, ["h", "e", "l", "l", "o"]);

        $parser = repeatList(3, repeat(3, alphaChar()));
        $this->assertParses("EURUSDGBP", $parser, ["EUR", "USD", "GBP"]);
    }
}
