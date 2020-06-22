<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\ParseResult;

use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Succeed;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;

final class AlternativeTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function alternative()
    {
        $succeed1 = new Succeed("S1", "");
        $succeed2 = new Succeed("S2", "");
        $fail1 = new Fail("F1", "");
        $fail2 = new Fail("F2", "");

        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($succeed2));
        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($fail1));
        $this->assertStrictlyEquals($succeed1, $fail1->alternative($succeed1));
        $this->assertStrictlyEquals($fail1, $fail1->alternative($fail2));
    }
}
