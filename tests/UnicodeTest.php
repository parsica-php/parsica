<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;

final class UnicodeTest extends ParserTestCase
{
    /** @test */
    public function mbstring_must_be_installed()
    {
        $this->assertSame("b", mb_substr("abc", 1, 1), "ext-mbstring is not installed.");
    }

    /** @test */
    public function parses_unicode()
    {
        $parser = char("🥰");
        $this->assertParse("🥰", $parser, "🥰 hello");
    }
}
