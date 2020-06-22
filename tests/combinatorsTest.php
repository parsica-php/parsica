<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\{alphaChar,
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
    many,
    noneOf,
    noneOfS,
    notFollowedBy,
    oneOf,
    oneOfS,
    optional,
    punctuationChar,
    sequence,
    skipSpace,
    string,
    stringI,
    takeRest,
    whitespace
};

final class combinatorsTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function identity()
    {
        $parser = identity(char('a'));
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("bc", $parser, "abc");
        $this->assertNotParse($parser, "bc", "char(a)", "identity shouldn't show up in error messages");
    }

    /** @test */
    public function thenIgnore()
    {
        $parser = string('abcd')
            ->thenIgnore(char('-'))
            ->append(string('efgh'));
        $this->assertParse("abcdefgh", $parser, "abcd-efgh");
        $this->assertNotParse($parser, "abcdefgh");

        // smae with optional dash
        $parser = string('abcd')
            ->thenIgnore(optional(char('-')))
            ->append(string('efgh'));
        $this->assertParse("abcdefgh", $parser, "abcdefgh");
        $this->assertParse("abcdefgh", $parser, "abcd-efgh");
    }


    /** @test */
    public function anySingle()
    {
        $parser = anySingle();
        $this->assertFailOnEOF($parser);
        $this->assertParse("a", $parser, "a");
        $this->assertParse("a", $parser, "abc");
        $this->assertParse(":", $parser, ":");
        $this->assertParse(":", $parser, ":-)");
    }

    /** @test */
    public function anySingleBut()
    {
        $parser = anySingleBut("x");
        $this->assertFailOnEOF($parser);
        $this->assertParse("a", $parser, "a");
        $this->assertRemain("", $parser, "a");
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("bc", $parser, "abc");
        $this->assertNotParse($parser, "x");
        $this->assertNotParse($parser, "xxx");
    }

    /** @test */
    public function oneOf()
    {
        $parser = oneOf(['a', 'b', 'c']);
        $this->assertFailOnEOF($parser);
        $this->assertParse("a", $parser, "a");
        $this->assertParse("a", $parser, "ax");
        $this->assertParse("b", $parser, "b");
        $this->assertParse("c", $parser, "c");
        $this->assertNotParse($parser, "xyz");
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
        $this->assertParse("a", $parser, "ax");
        $this->assertNotParse($parser, "xyz");
    }

    /** @test */
    public function noneOf()
    {
        $parser = noneOf(['a', 'b', 'c']);
        $this->assertFailOnEOF($parser);
        $this->assertNotParse($parser, "a");
        $this->assertNotParse($parser, "ax");
        $this->assertNotParse($parser, "b");
        $this->assertParse("x", $parser, "xyz");
        $this->assertRemain("yz", $parser, "xyz");
    }

    /** @test */
    public function noneOfS()
    {
        $parser = noneOfS("abc");
        $this->assertNotParse($parser, "ax");
        $this->assertParse("x", $parser, "xyz");
    }

    /** @test */
    public function takeRest()
    {
        $parser = takeRest();
        $this->assertSucceedOnEOF($parser);
        $this->assertParse("xyz", $parser, "xyz");
        $this->assertRemain("", $parser, "xyz");
    }


    /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));
        $this->assertFailOnEOF($parser);
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("bc", $parser, "abc");
        $this->assertParse("b", $parser, "bc");
        $this->assertRemain("c", $parser, "bc");
        $this->assertNotParse($parser, "cd");
    }

    /** @test */
    public function sequence()
    {
        $parser = sequence(char('a'), char('b'));
        $this->assertFailOnEOF($parser);
        $this->assertParse("b", $parser, "abc");
        $this->assertRemain("c", $parser, "abc");
        $this->assertNotParse($parser, "acc");
        $this->assertNotParse($parser, "cab");
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
        $this->assertParse($expected, $parser, "Hello , world!");
        $this->assertParse($expected, $parser, "Hello,world!");
    }

    /** @test */
    public function collectFails()
    {
        $parser =
            collect(
                string("Hello"),
                string("world")
            );
        $this->assertFailOnEOF($parser);
        $this->assertNotParse($parser, "Helloplanet");
    }

    /**
     * @test
     */
    public function atLeastOne()
    {
        $parser = atLeastOne(char('a'));
        $this->assertFailOnEOF($parser);
        $this->assertParse("a", $parser, "a");
        $this->assertParse("aa", $parser, "aa");
        $this->assertParse("aaaaa", $parser, "aaaaa");
        $this->assertParse("aaa", $parser, "aaabb");
        $this->assertNotParse($parser, "bb");
    }

    /** @test */
    public function any_()
    {
        $symbol = any(string("€"), string("$"));
        $amount = float()->map('floatval');
        $money = collect($symbol, $amount);

        $this->assertFailOnEOF($money);
        $this->assertParse("€", $symbol, "€");
        $this->assertParse(15.23, $amount, "15.23");
        $this->assertParse(["€", 15.23], $money, "€15.23");
        $this->assertParse(["$", 15.0], $money, "$15");
        $this->assertNotParse($money, "£12.13");
    }

    /** @test */
    public function choice()
    {
        $symbol = choice(string("€"), string("$"));
        $amount = float()->map('floatval');
        $money = collect($symbol, $amount);

        $this->assertFailOnEOF($money);
        $this->assertParse("€", $symbol, "€");
        $this->assertParse(15.23, $amount, "15.23");
        $this->assertParse(["€", 15.23], $money, "€15.23");
        $this->assertParse(["$", 15.0], $money, "$15");
        $this->assertNotParse($money, "£12.13");
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
        $this->assertParse(23, $parser, $input);
    }

    /** @test */
    public function between()
    {
        $parser = between(char('{'), char('}'), atLeastOne(alphaNumChar()));
        $input = "{foo}";
        $this->assertParse("foo", $parser, $input);
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

    /** @test */
    public function lookAhead()
    {
        $this->markTestSkipped("This will become relevant when we change alternative() behaviour");

        // TEST:
        /*
        $parser = lookAhead(stringI("hello"));

        // On success, lookAhead succeeds without consuming input
        $this->assertParse("", $parser, "Hello, world!");
        $this->assertRemain("Hello, world!", $parser, "Hello, world!");

        // On fail, lookAhead fails without consuming input
        $this->assertNotParse($parser, "Hi, world!");
        */

        //Impl
        /**
         * If $parser succeeds (either consuming input or not), lookAhead behaves like $parser succeeded without consuming
         * anything. If $parser fails, lookAhead has no effect, i.e. it will fail to consume input if $parser fails consuming
         * input.
         *
         * @template T
         * @param Parser<T> $parser
         * @return Parser<T>
         */
        /*
        function lookAhead(Parser $parser): Parser
        {
            return Parser::make(
                fn(string $input): ParseResult => $parser->run($input)->isSuccess()
                    ? new Succeed("", $input)
                    : new Fail("lookAhead", $input)
            );
        }
        */
    }


}
