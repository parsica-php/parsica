<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\Internal;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Internal\Position;
use Parsica\Parsica\StringStream;
use function Parsica\Parsica\char;

final class PositionTest extends TestCase
{
    /** @test */
    public function update()
    {
        $position = Position::initial();
        $this->assertEquals(1, $position->line());
        $this->assertEquals(1, $position->column());
        $position = $position->advance("a");
        $this->assertEquals(1, $position->line());
        $this->assertEquals(2, $position->column());
        $position = $position->advance("\n");
        $this->assertEquals(2, $position->line());
        $this->assertEquals(1, $position->column());
        $position = $position->advance("\n\n\nabc");
        $this->assertEquals(5, $position->line());
        $this->assertEquals(4, $position->column());
    }

    /** @test */
    public function position_in_sequence()
    {
        $parser = char('a')->followedBy(char('b'));
        $input = new StringStream("abc", Position::initial());
        $result = $parser->run($input);

        $expectedColumn = 3;
        $actualColumn = $result->remainder()->position()->column();
        $this->assertEquals($expectedColumn, $actualColumn);
    }

    /** @test */
    public function position_with_tabs()
    {
        $expected = 10;
        // All of these move the column position to 10
        $position = Position::initial()->advance("123456789");
        $this->assertEquals($expected, $position->column());
        $position = Position::initial()->advance("\t56789");
        $this->assertEquals($expected, $position->column());
        $position = Position::initial()->advance("\t\t9");
        $this->assertEquals($expected, $position->column());
        $position = Position::initial()->advance("1\t56789");
        $this->assertEquals($expected, $position->column());
        $position = Position::initial()->advance("123\t56789");
        $this->assertEquals($expected, $position->column());
    }
}
