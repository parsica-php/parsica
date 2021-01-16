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

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Succeed;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use Verraes\Parsica\StringStream;

final class AppendTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function append_strings()
    {
        $succeed1 = new Succeed("Parsed1", StringStream::fromString("Remain1"));
        $succeed2 = new Succeed("Parsed2", StringStream::fromString("Remain2"));
        $fail1 = new Fail("Expected1", StringStream::fromString("Got1"));
        $fail2 = new Fail("Expected2", StringStream::fromString("Got2"));

        $this->assertEquals(new Succeed("Parsed1Parsed2", StringStream::fromString("Remain2")), $succeed1->append($succeed2));
        $this->assertEquals(new Fail("Expected1", StringStream::fromString("Got1")), $succeed1->append($fail1));
        $this->assertEquals(new Fail("Expected1", StringStream::fromString("Got1")), $fail1->append($succeed2));
        $this->assertEquals(new Fail("Expected1", StringStream::fromString("Got1")), $fail1->append($fail2));
    }

    /** @test */
    public function append_with_null()
    {
        $null1 = new Succeed(null, StringStream::fromString("Remain Null 1"));
        $null2 = new Succeed(null, StringStream::fromString("Remain Null 2"));
        $string = new Succeed("String", StringStream::fromString("Remain String"));

        $first = $string->append($null1);
        $this->assertEquals(new Succeed("String", StringStream::fromString("Remain Null 1")), $first);

        $second = $null1->append($string);
        $this->assertEquals(new Succeed("String", StringStream::fromString("Remain String")), $second);

        $both = $null1->append($null2);
        $this->assertEquals(new Succeed(null, StringStream::fromString("Remain Null 2")), $both);
    }
}
