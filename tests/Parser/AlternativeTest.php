<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Parser;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\alphaChar;
use function Verraes\Parsica\char;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\either;
use function Verraes\Parsica\eof;
use function Verraes\Parsica\ignore;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\many;
use function Verraes\Parsica\punctuationChar;
use function Verraes\Parsica\some;
use function Verraes\Parsica\string;

final class AlternativeTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function or()
    {
        $parser = char('a')->or(char('b'));
        $this->assertParses("a123", $parser, "a");
        $this->assertParses("b123", $parser, "b");
        $this->assertParseFails("123", $parser);
    }

    /** @test */
    public function alternatives_for_strings_with_similar_starts()
    {
        $jan =
            either(
                string("Jan")->thenEof(),
                string("January")->thenEof(),
            );
        $this->assertParses("Jan", $jan, "Jan");
        $this->assertParses("January", $jan, "January");

        // Reverse order
        $jan =
            either(
                string("January")->thenEof(),
                string("Jan")->thenEof(),
            );
        $this->assertParses("Jan", $jan, "Jan");
        $this->assertParses("January", $jan, "January");

    }

    /** @test */
    public function or_order_matters()
    {
        // The order of clauses in an or() matters. If we do the following parser definition, the parser will consume
        // "http", even if the strings starts with "https", leaving "s://..." as the remainder.
        $parser = string('http')->or(string('https'));
        $input = "https://verraes.net";
        $this->assertRemainder($input, $parser, "s://verraes.net");

        // The solution is to consider the order of or clauses:
        $parser = string('https')->or(string('http'));
        $input = "https://verraes.net";
        $this->assertParses($input, $parser, "https");
        $this->assertRemainder($input, $parser, "://verraes.net");
    }

    /** @test */
    public function optional()
    {
        $parser = char('a')->optional();
        $this->assertParses("", $parser, null, "EOF");
        $this->assertParses("abc", $parser, "a");
        $this->assertRemainder("abc", $parser, "bc");

        $this->assertParses("bc", $parser, null);
        $this->assertRemainder("bc", $parser, "bc");
    }

    /** @test */
    public function many()
    {
        $parser = many(alphaChar());
        $this->assertParses("123", $parser, []);
        $this->assertParses("Hello", $parser, ["H", "e", "l", "l", "o"]);

        $parser = many(alphaChar()->append(digitChar()));
        $this->assertParses("1a2b3c", $parser, []);
        $this->assertParses("a1b2c3", $parser, ["a1", "b2", "c3"]);

    }

    /** @test */
    public function some()
    {
        $parser = many(
            keepFirst(
                some(alphaChar())->map(fn($a) => implode('', $a)),
                punctuationChar()->optional()
            )
        );
        $input = "abc,def,ghi";
        $expected = ["abc","def","ghi"];
        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function some_2()
    {
        $parser = some(string("foo"));
        $this->assertParseFails("bla", $parser);
        $this->assertParses("foo", $parser, ["foo"]);
        $this->assertParses("foobar", $parser, ["foo"]);
        $this->assertParses("foofoo", $parser, ["foo", "foo"]);
        $this->assertParses("foofoobar", $parser, ["foo", "foo"]);
    }

}
