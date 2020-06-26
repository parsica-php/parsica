<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_3_0\ParseResult;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Succeed;
use Verraes\Parsica\PHPUnit\ParserAssertions;

final class AppendTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function append_strings()
    {
        $succeed1 = new Succeed("Parsed1", "Remain1");
        $succeed2 = new Succeed("Parsed2", "Remain2");
        $fail1 = new Fail("Expected1", "Got1");
        $fail2 = new Fail("Expected2", "Got2");

        $this->assertStrictlyEquals(new Succeed("Parsed1Parsed2", "Remain2"), $succeed1->append($succeed2));
        $this->assertStrictlyEquals(new Fail("Expected1", "Got1"), $succeed1->append($fail1));
        $this->assertStrictlyEquals(new Fail("Expected1", "Got1"), $fail1->append($succeed2));
        $this->assertStrictlyEquals(new Fail("Expected1", "Got1"), $fail1->append($fail2));
    }
}
