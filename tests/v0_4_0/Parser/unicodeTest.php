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
