<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\Parser;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\char;
use function Parsica\Parsica\string;

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
