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
use Parsica\Parsica\StringStream;
use function Parsica\Parsica\char;

final class ParseResultTest extends TestCase
{

    /** @test */
    public function ParseSuccess_continueWith()
    {
        $input = new StringStream("abc");
        $success = char('a')->run($input);
        $result = $success->continueWith(char('b'));
        $this->assertTrue($result->isSuccess());
        $this->assertEquals("c", $result->remainder());
    }

    /** @test */
    public function ParseFailure_continueWith()
    {
        $input = new StringStream("abc");
        $fail = char('x')->run($input);
        $result = $fail->continueWith(char('a'));
        $this->assertTrue($result->isFail());
    }

}
