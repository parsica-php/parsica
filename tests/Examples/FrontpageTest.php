<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Examples;

use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\alphaChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;

final class FrontpageTest extends TestCase
{
    /** @test */
    public function example_on_frontpage()
    {
        $parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
        $result = $parser->try("{Hello}");
        echo $result->output(); // Hello

        $this->assertEquals('Hello', $result->output());
    }
}
