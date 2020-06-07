<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\string;

final class ParserTest extends ParserTestCase
{
    /** @test */
    public function label()
    {
        $parser = string(":-)");
        $this->assertNotParse($parser, "x", "string(:-))");

        $labeled = $parser->label("smiley");
        $this->assertNotParse($labeled, "x", "smiley");
    }

    /** @test */
    public function followedBy()
    {
        $parser = char('a')->followedBy(char('b'));
        $this->assertParse("b", $parser, "abc");
    }
}
