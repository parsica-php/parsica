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
        $position = Position::initial(8);
        $this->assertEquals(1, $position->line());
        $this->assertEquals(1, $position->column());
        $this->assertEquals(8, $position->totalBytes());
        $this->assertEquals(8, $position->unreadBytes());
        $this->assertEquals(0, $position->bytePosition());
        $position = $position->advance("a");
        $this->assertEquals(1, $position->line());
        $this->assertEquals(2, $position->column());
        $this->assertEquals(7, $position->unreadBytes());
        $this->assertEquals(1, $position->bytePosition());
        $position = $position->advance("\n");
        $this->assertEquals(2, $position->line());
        $this->assertEquals(1, $position->column());
        $this->assertEquals(6, $position->unreadBytes());
        $this->assertEquals(2, $position->bytePosition());
        $position = $position->advance("\n\n\nabc");
        $this->assertEquals(5, $position->line());
        $this->assertEquals(4, $position->column());
        $this->assertEquals(0, $position->unreadBytes());
        $this->assertEquals(8, $position->bytePosition());
    }

    /**
     * The german word Über (over) is counted as 5:4 based on strlen:mb_strlen respectively.
     * @test
     */
    public function multibyte_update()
    {
        $position = Position::initial(strlen('Über'));
        $this->assertEquals(1, $position->line());
        $this->assertEquals(1, $position->column());
        $this->assertEquals(5, $position->totalBytes());
        $this->assertEquals(5, $position->unreadBytes());
        $this->assertEquals(0, $position->bytePosition());
        $position = $position->advance("Ü");
        $this->assertEquals(1, $position->line());
        $this->assertEquals(2, $position->column());
        $this->assertEquals(3, $position->unreadBytes());
        $this->assertEquals(2, $position->bytePosition());
        $position = $position->advance("b");
        $this->assertEquals(1, $position->line());
        $this->assertEquals(3, $position->column());
        $this->assertEquals(2, $position->unreadBytes());
        $this->assertEquals(3, $position->bytePosition());
        $position = $position->advance("er");
        $this->assertEquals(1, $position->line());
        $this->assertEquals(5, $position->column());
        $this->assertEquals(0, $position->unreadBytes());
        $this->assertEquals(5, $position->bytePosition());
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
