<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\ParseResult;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\Internal\Succeed;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use Parsica\Parsica\StringStream;

final class AppendTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function append_strings()
    {
        $remainder = new StringStream("");
        $succeed1 = new Succeed("Parsed1", new StringStream("Remain1"));
        $succeed2 = new Succeed("Parsed2", new StringStream("Remain2"));
        $fail1 = new Fail("Expected1", new StringStream("Got1"));
        $fail2 = new Fail("Expected2", new StringStream("Got2"));

        $this->assertStrictlyEquals(new Succeed("Parsed1Parsed2", new StringStream("Remain2")), $succeed1->append($succeed2));
        $this->assertStrictlyEquals(new Fail("Expected1", new StringStream("Got1")), $succeed1->append($fail1));
        $this->assertStrictlyEquals(new Fail("Expected1", new StringStream("Got1")), $fail1->append($succeed2));
        $this->assertStrictlyEquals(new Fail("Expected1", new StringStream("Got1")), $fail1->append($fail2));
    }
    /** @test */
    public function append_with_null()
    {
        $null1 = new Succeed(null, new StringStream("Remain Null 1"));
        $null2 = new Succeed(null, new StringStream("Remain Null 2"));
        $string = new Succeed("String", new StringStream("Remain String"));

        $first = $string->append($null1);
        $this->assertStrictlyEquals(new Succeed("String", new StringStream("Remain Null 1")), $first);

        $second = $null1->append($string);
        $this->assertStrictlyEquals(new Succeed("String", new StringStream("Remain String")), $second);

        $both = $null1->append($null2);
        $this->assertStrictlyEquals(new Succeed(null, new StringStream("Remain Null 2")), $both);
    }
}
