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
use function Verraes\Parsica\string;

final class ParserTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function label()
    {
        $parser = string(":-)");
        $this->assertParseFails("x", $parser, "':-)'");

        $labeled = $parser->label("smiley");
        $this->assertParseFails("x", $labeled, "smiley");
    }

    /** @test */
    public function followedBy()
    {
        $parser = char('a')->followedBy(char('b'));
        $this->assertParses("abc", $parser, "b");
    }
}
