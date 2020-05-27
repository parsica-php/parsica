<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinator\equals;

final class PredicatesTest extends ParserTest
{
    /** @test */
    public function equals()
    {
        $this->assertTrue(equals('x')('x'));
        $this->assertFalse(equals('x')('y'));
    }
}

