<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica\Parser;

use Verraes\Parsica\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\char;

final class unicodeTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function mbstring_must_be_installed()
    {
        $this->assertTrue(function_exists('mb_detect_encoding'), "ext-mbstring must be installed.");
    }

    /** @test */
    public function parses_unicode()
    {
        $parser = char("🥰");
        $this->assertParse("🥰", $parser, "🥰 hello");
    }
}
