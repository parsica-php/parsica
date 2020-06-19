<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica\Parser;

use Verraes\Parsica\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\char;
use function Verraes\Parsica\string;

final class ParserTest extends TestCase
{
    use ParserAssertions;

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
