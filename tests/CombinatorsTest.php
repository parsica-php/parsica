<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinator\{any,
    atLeastOne,
    char,
    collect,
    digit,
    either,
    float,
    ignore,
    into1,
    intoNew1,
    optional,
    seq,
    space,
    string
};

final class CombinatorsTest extends ParserTest
{

    /** @test */
    public function ignore()
    {
        $parser = ignore(char('a'));
        $this->assertParse($parser, "abc", "");
        $this->assertRemain($parser, "abc", "bc");

        $parser = string('abcd')
            ->followedBy(ignore(char('-')))
            ->followedBy(string('efgh'));
        $this->assertParse($parser, "abcd-efgh", "abcdefgh");
    }

    /** @test */
    public function optional()
    {
        $parser = char('a')->optional();
        $this->assertParse($parser, "abc", "a");
        $this->assertRemain($parser, "abc", "bc");

        $this->assertParse($parser, "bc", "");
        $this->assertRemain($parser, "bc", "bc");

        $parser = string('abcd')
            ->followedBy(optional(ignore(char('-'))))
            ->followedBy(string('efgh'));
        $this->assertParse($parser, "abcd-efgh", "abcdefgh");
        $this->assertParse($parser, "abcdefgh", "abcdefgh");
    }

    /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));

        $this->assertParse($parser, "abc", "a");
        $this->assertRemain($parser, "abc", "bc");
        $this->assertParse($parser, "bc", "b");
        $this->assertRemain($parser, "bc", "c");
        $this->assertNotParse($parser, "cd");
    }

    /** @test */
    public function seq()
    {
        $parser = seq(char('a'), char('b'));

        $this->assertParse($parser, "abc", "ab");
        $this->assertRemain($parser, "abc", "c");
        $this->assertNotParse($parser, "acc");
        $this->assertNotParse($parser, "cab");
    }

    /** @test */
    public function collect()
    {
        $parser =
            collect(
                string("Hello")
                    ->followedBy(
                        optional(space())->ignore()
                    )
                    ->followedBy(
                        char(',')->ignore()
                    )
                    ->followedBy(
                        optional(space())->ignore()
                    ),
                string("world")
                    ->followedBy(
                        char('!')->ignore()
                    )
            );

        $expected = ["Hello", "world"];

        $this->assertParse($parser, "Hello , world!", $expected);
        $this->assertParse($parser, "Hello,world!", $expected);
    }

    /** @test */
    public function collectFails()
    {
        $parser =
            collect(
                string("Hello"),
                string("world")
            );
        $this->assertNotParse($parser, "Helloplanet");
    }

    /**
     * @test
     */
    public function atLeastOne()
    {
        $parser = atLeastOne(char('a'));
        $this->assertParse($parser, "a", "a");
        $this->assertParse($parser, "aa", "aa");
        $this->assertParse($parser, "aabb", "aa");
        $this->assertNotParse($parser, "bb");
    }

    /** @test */
    public function any_()
    {
        $symbol = any(string("€"), string("$"));
        $amount = float()->into1('floatval');
        $money = collect($symbol, $amount);

        $this->assertParse($symbol, "€", "€");
        $this->assertParse($amount, "15.23", 15.23);
        $this->assertParse($money, "€15.23", ["€", 15.23]);
        $this->assertParse($money, "$15", ["$", 15]);
        $this->assertNotParse($money, "£12.13");
    }

}
