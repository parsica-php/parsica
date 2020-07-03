<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_4_0\Internal;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Internal\Position;

final class PositionTest extends TestCase
{
    /** @test */
    public function update()
    {
        $position = Position::initial();
        $this->assertEquals(1, $position->line());
        $this->assertEquals(1, $position->column());
        $position = $position->update("a");
        $this->assertEquals(1, $position->line());
        $this->assertEquals(2, $position->column());
        $position = $position->update("\n");
        $this->assertEquals(2, $position->line());
        $this->assertEquals(1, $position->column());
        $position = $position->update("\n\n\nabc");
        $this->assertEquals(5, $position->line());
        $this->assertEquals(4, $position->column());
    }

}
