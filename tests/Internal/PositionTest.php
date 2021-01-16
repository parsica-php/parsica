<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Internal;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Internal\ActualPosition;
use Verraes\Parsica\Internal\NovelImmutablePosition;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\char;

final class PositionTest extends TestCase
{
    /** @test */
    public function update()
    {
        $position = NovelImmutablePosition::initial();
        $this->assertEquals(0, $position->pointer());
        $this->assertEquals(1, $position->line());
        $this->assertEquals(1, $position->column());
        $position = $position->advance("a");
        $this->assertEquals(1, $position->pointer());
        $this->assertEquals(1, $position->line());
        $this->assertEquals(2, $position->column());
        $position = $position->advance("\n");
        $this->assertEquals(2, $position->pointer());
        $this->assertEquals(2, $position->line());
        $this->assertEquals(1, $position->column());
        $position = $position->advance("\n");
        $this->assertEquals(3, $position->pointer());
        $this->assertEquals(3, $position->line());
        $this->assertEquals(1, $position->column());
        $position = $position->advance("a");
        $this->assertEquals(4, $position->pointer());
        $this->assertEquals(3, $position->line());
        $this->assertEquals(2, $position->column());
    }

    /** @test */
    public function position_in_sequence()
    {
        $parser = char('a')->followedBy(char('b'));
        $input = StringStream::fromString("abc");
        $result = $parser->run($input);

        $expectedColumn = 3;
        $actualColumn = $result->remainder()->position()->column();
        $this->assertEquals($expectedColumn, $actualColumn);
    }

    /** @test */
    public function position_with_tabs()
    {
        $position = NovelImmutablePosition::initial();
        $position = $position->advance("\t");
        $this->assertEquals(5, $position->column());
        $position = $position->advance("\t");
        $this->assertEquals(9, $position->column());
        $position = $position->advance("a");
        $this->assertEquals(10, $position->column());
        $position = $position->advance("\t");
        $this->assertEquals(13, $position->column());

    }
}
