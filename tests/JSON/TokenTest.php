<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\JSON;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\JSON\JSON;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\char;
use function Parsica\Parsica\JSON\token;

final class TokenTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function token()
    {
        $parser = JSON::token(char('a'));
        $input = "a  \n   \tb";
        $this->assertRemainder($input, $parser, "b");
    }

}

