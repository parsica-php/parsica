<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_4_0\Parser;

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
        $this->assertParse("a", $parser, "a123");
        $this->assertParse("b", $parser, "b123");
        $this->assertNotParse($parser, "123");
    }

    /** @test */
    public function alternatives_for_strings_with_similar_starts()
    {
        $jan =
            either(
                string("Jan")->thenIgnore(eof()),
                string("January")->thenIgnore(eof()),
            );
        $this->assertParse("Jan", $jan, "Jan");
        $this->assertParse("January", $jan, "January");

        // Reverse order
        $jan =
            either(
                string("January")->thenIgnore(eof()),
                string("Jan")->thenIgnore(eof()),
            );
        $this->assertParse("Jan", $jan, "Jan");
        $this->assertParse("January", $jan, "January");

    }

    /** @test */
    public function or_order_matters()
    {
        // The order of clauses in an or() matters. If we do the following parser definition, the parser will consume
        // "http", even if the strings starts with "https", leaving "s://..." as the remainder.
        $parser = string('http')->or(string('https'));
        $input = "https://verraes.net";
        $this->assertRemain("s://verraes.net", $parser, $input);

        // The solution is to consider the order of or clauses:
        $parser = string('https')->or(string('http'));
        $input = "https://verraes.net";
        $this->assertParse("https", $parser, $input);
        $this->assertRemain("://verraes.net", $parser, $input);
    }

    /** @test */
    public function optional()
    {
        $parser = char('a')->optional();
        $this->assertParse(null, $parser, "", "EOF");
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("bc", $parser, "abc");

        $this->assertParse(null, $parser, "bc");
        $this->assertRemain("bc", $parser, "bc");
    }

    /** @test */
    public function many()
    {
        $parser = many(alphaChar());
        $this->assertParse([], $parser, "123");
        $this->assertParse(["H", "e", "l", "l", "o"], $parser, "Hello");

        $parser = many(alphaChar()->append(digitChar()));
        $this->assertParse([], $parser, "1a2b3c");
        $this->assertParse(["a1", "b2", "c3"], $parser, "a1b2c3");

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
        $this->assertParse($expected, $parser, $input);
    }

    /** @test */
    public function some_2()
    {
        $parser = some(string("foo"));
        $this->assertNotParse($parser, "bla");
        $this->assertParse(["foo"], $parser, "foo");
        $this->assertParse(["foo"], $parser, "foobar");
        $this->assertParse(["foo", "foo"], $parser, "foofoo");
        $this->assertParse(["foo", "foo"], $parser, "foofoobar");
    }

}
