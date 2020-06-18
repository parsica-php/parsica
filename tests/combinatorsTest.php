<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use InvalidArgumentException;
use Mathias\ParserCombinator\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Mathias\ParserCombinator\{alphaChar,
    alphaNumChar,
    any,
    anySingle,
    anySingleBut,
    atLeastOne,
    between,
    char,
    collect,
    digitChar,
    either,
    float,
    identity,
    keepFirst,
    many,
    noneOf,
    noneOfS,
    oneOf,
    oneOfS,
    optional,
    punctuationChar,
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
    public function skipMany()
    {
        //skipMany p applies the parser p zero or more times, skipping its result.

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
        $parser = between(char('{'), atLeastOne(alphaNumChar()), char('}'));
        $input = "{foo}";
        $this->assertParse("foo", $parser, $input);
    }
}
