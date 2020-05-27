<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use PHPUnit\Framework\TestCase;

final class MBStringTest extends TestCase
{
    /** @test */
    public function mbstring_should_be_installed()
    {
        $this->assertSame("b", mb_substr("abc", 1, 1), "ext-mbstring is not installed.");
    }
}
