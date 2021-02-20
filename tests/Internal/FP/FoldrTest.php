<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\Internal\FP;

use PHPUnit\Framework\TestCase;
use function Parsica\Parsica\Internal\FP\foldr;

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
        $input = [1, 2, 3, 4, 5];
        $init = 0;

        // foldl: ((((0 - 1) - 2) - 3) - 4) - 5) = -15
        // foldr: (1 - (2 - (3 - (4 - (5 - 0))))) = 3

        $actual = array_reduce($input, $minus, $init);
        $this->assertSame(-15, $actual);

        $actual = foldr($input, $minus, $init);
        $this->assertSame(3, $actual);
    }

    /** @test */
    public function x()
    {
        $concat = fn($x, $y) => "$x$y";
        $input = [1, 2, 3, 4, 5];
        $init = "0";

        // foldl: 012345
        // foldr: 123450

        $actual = array_reduce($input, $concat, $init);
        $this->assertSame("012345", $actual);

        $actual = foldr($input, $concat, $init);
        $this->assertSame("123450", $actual);
    }

}
