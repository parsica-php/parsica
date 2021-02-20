<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\{andPred, isEqual, notPred, orPred, satisfy};

final class predicatesTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function isEqual()
    {
        $this->assertTrue(isEqual('x')('x'));
        $this->assertFalse(isEqual('x')('y'));
    }

    /** @test */
    public function notPred()
    {
        $this->assertFalse(notPred(isEqual('x'))('x'));
        $this->assertTrue(notPred(isEqual('x'))('y'));

        $parser = satisfy(notPred(isEqual('x')));
        $this->assertParseFails("xyz", $parser);
        $this->assertParses("yz", $parser, "y");
        $this->assertParseFails("", $parser, "satisfy(predicate)");
    }

    /** @test */
    public function orPred()
    {
        $predicate = orPred(isEqual('x'), isEqual('y'));
        $this->assertTrue($predicate('x'));
        $this->assertTrue($predicate('y'));
        $this->assertFalse($predicate('z'));
    }

    /** @test */
    public function andPred()
    {
        $predicate = andPred(isEqual('x'), isEqual('x'));
        $this->assertTrue($predicate('x'));
        $this->assertFalse($predicate('y'));
    }
}

