<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\JSON;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\char;
use function Verraes\Parsica\JSON\token;

final class TokenTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function token()
    {
        $parser = token(char('a'));
        $input = "a  \n   \tb";
        $this->assertRemain("b", $parser, $input);
    }

}

