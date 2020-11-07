<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Internal\FP;

use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\Internal\FP\foldr;

final class FoldrTest extends TestCase
{
    /** @test */
    public function sum_implemented_as_foldr()
    {
        $actual = foldr([1, 2, 3], fn ($x, $y) => $x + $y, 0);
        $this->assertSame(6, $actual);
    }

    /** @test */
    public function associativity_is_correct()
    {
        $minus = fn($x, $y) => $x - $y;
        $input = [1, 2, 3];
        $init = 0;

        // foldl: ((0 - 1) - 2) - 3 = -6
        // foldr: (1 - (2 - (3 - 0))) = 2

        $actual = array_reduce($input, $minus, $init);
        $this->assertSame(-6, $actual);

        $actual = foldr($input, $minus, $init);
        $this->assertSame(2, $actual);
    }

}
