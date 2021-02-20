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
use Parsica\Parsica\ParserHasFailed;
use Parsica\Parsica\StringStream;
use function Parsica\Parsica\char;
use function Parsica\Parsica\skipSpace;
use function Parsica\Parsica\string;

final class RunningParsersTest extends TestCase
{
    /** @test */
    public function try_throws()
    {
        $parser = char('a');
        $result = $parser->try(new StringStream("a"));
        $this->assertSame("a", $result->output());

        $this->expectException(ParserHasFailed::class);
        $result = $parser->try(new StringStream("b"));
    }

    /** @test */
    public function continueFrom()
    {
        $parser = string('hello')->sequence(skipSpace());
        $result = $parser->try(new StringStream("hello world!"));
        $parser2 = string("world");
        $result2 = $parser2->continueFrom($result);
        $this->assertEquals("world", $result2->output());
        $this->assertEquals("!", $result2->remainder());
    }
}
