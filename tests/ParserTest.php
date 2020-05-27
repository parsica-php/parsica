<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser;
use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use PHPUnit\Framework\TestCase;
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
}
