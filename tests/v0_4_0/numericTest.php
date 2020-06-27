<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_4_0;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\float;

final class numericTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function float()
    {
        $this->assertParse("0", float(), "0");
        $this->assertParse("0.1", float(), "0.1");
        $this->assertParse("123.456", float(), "123.456");
        $this->assertParse(123.456, float()->map('floatval'), "123.456");
    }
}
