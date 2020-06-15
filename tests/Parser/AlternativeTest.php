<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\either;
use function Mathias\ParserCombinator\eof;
use function Mathias\ParserCombinator\ignore;
use function Mathias\ParserCombinator\string;

final class AlternativeTest extends ParserTestCase
{
    /** @test */
    public function alternative()
    {
        $parser = char('a')->alternative(char('b'));
        $this->assertParse("a", $parser, "a123");
        $this->assertParse("b", $parser, "b123");
        $this->assertNotParse($parser, "123");
    }

    /** @test */
    public function alternatives_for_strings_with_similar_starts()
    {
        $jan =
            either(
                string("Jan")->append(ignore(eof())),
                string("January")->append(ignore(eof())),
            );
        $this->assertParse("Jan", $jan, "Jan");
        $this->assertParse("January", $jan, "January");

        // Reverse order
        $jan =
            either(
                string("January")->append(ignore(eof())),
                string("Jan")->append(ignore(eof())),
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


}
