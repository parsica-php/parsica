<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_4_0\ParseResult;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\StringStream;
use Verraes\Parsica\Internal\Succeed;
use Verraes\Parsica\PHPUnit\ParserAssertions;

final class AlternativeTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function alternative()
    {
        $stream = new StringStream("");
        $succeed1 = new Succeed("S1", $stream);
        $succeed2 = new Succeed("S2", $stream);
        $fail1 = new Fail("F1", $stream);
        $fail2 = new Fail("F2", $stream);

        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($succeed2));
        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($fail1));
        $this->assertStrictlyEquals($succeed1, $fail1->alternative($succeed1));
        $this->assertStrictlyEquals($fail1, $fail1->alternative($fail2));
    }
}
