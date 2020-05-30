<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use http\Exception\InvalidArgumentException;
use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{any,
    anySingle,
    anySingleBut,
    atLeastOne,
    char,
    collect,
    either,
    float,
    identity,
    ignore,
    noneOf,
    noneOfS,
    oneOf,
    oneOfS,
    optional,
    seq,
    skipSpace,
    string,
    takeRest};

final class combinatorsTest extends ParserTestCase
{
    /** @test */
    public function identity()
    {
        $parser = identity(char('a'));
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("bc", $parser, "abc");
        $this->assertNotParse($parser, "bc", "char(a)", "identity shouldn't show up in error messages");
    }

    /** @test */
    public function ignore()
    {
        $parser = ignore(char('a'));
        $this->assertFailOnEOF($parser);
        $this->assertParse("", $parser, "abc");
        $this->assertRemain("bc", $parser, "abc");

        $parser = string('abcd')
            ->followedBy(ignore(char('-')))
            ->followedBy(string('efgh'));
        $this->assertParse("abcdefgh", $parser, "abcd-efgh");
    }

    /** @test */
    public function optional()
    {
        $parser = char('a')->optional();
        $this->assertSucceedOnEOF($parser);
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("bc", $parser, "abc");

        $this->assertParse("", $parser, "bc");
        $this->assertRemain("bc", $parser, "bc");

        $parser = string('abcd')
            ->followedBy(optional(ignore(char('-'))))
            ->followedBy(string('efgh'));
        $this->assertParse("abcdefgh", $parser, "abcd-efgh");
        $this->assertParse("abcdefgh", $parser, "abcdefgh");
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
        $this->expectException(\InvalidArgumentException::class);
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
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

    /** @test */
    public function seq()
    {
        $parser = seq(char('a'), char('b'));
        $this->assertFailOnEOF($parser);
        $this->assertParse("ab", $parser, "abc");
        $this->assertRemain("c", $parser, "abc");
        $this->assertNotParse($parser, "acc");
        $this->assertNotParse($parser, "cab");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

    /** @test */
    public function collect()
    {
        $parser =
            collect(
                string("Hello")
                    ->followedBy(skipSpace())
                    ->followedBy(char(',')->ignore())
                    ->followedBy(skipSpace()),
                string("world")
                    ->followedBy(char('!')->ignore())
            );

        $expected = ["Hello", "world"];
        $this->assertFailOnEOF($parser);
        $this->assertParse($expected, $parser, "Hello , world!");
        $this->assertParse($expected, $parser, "Hello,world!");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
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
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
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
        $this->assertParse("aa", $parser, "aabb");
        $this->assertNotParse($parser, "bb");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

    /** @test */
    public function any_()
    {
        $symbol = any(string("€"), string("$"));
        $amount = float()->fmap('floatval');
        $money = collect($symbol, $amount);

        $this->assertFailOnEOF($money);
        $this->assertParse("€", $symbol, "€");
        $this->assertParse(15.23, $amount, "15.23");
        $this->assertParse(["€", 15.23], $money, "€15.23");
        $this->assertParse(["$", 15], $money, "$15");
        $this->assertNotParse($money, "£12.13");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

}
