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
use function Parsica\Parsica\Curry\curry;

final class CurryTest extends TestCase
{
    /** @test */
    public function curry()
    {
        $f = fn($a, $b, $c) => $a + $b + $c;
        $curried = curry($f);

        $this->assertIsCallable($curried);
        $this->assertIsCallable($curried(1));
        $this->assertIsCallable($curried(1)(2));
        $this->assertIsCallable($curried(1)(2));

        $this->assertEquals(6, $curried(1)(2)(3));
    }

    /** @test */
    public function partial_application()
    {
        $f = fn($a, $b, $c) => $a + $b + $c;

        $this->assertIsCallable(curry($f, 1));
        $this->assertIsCallable(curry($f, 1, 2));

        // I would expect this:
//        $this->assertEquals(6, curry($f, 1, 2, 3));

        // But we must add a () at the end, which I feel is a bug:
        $this->assertIsCallable(curry($f, 1, 2, 3));
        $this->assertEquals(6, curry($f, 1, 2, 3)());
    }
}
