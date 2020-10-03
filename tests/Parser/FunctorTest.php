<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_4_0\Parser;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\char;
use function Verraes\Parsica\float;
use function Verraes\Parsica\sequence;

final class FunctorTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function map()
    {
        $parser =
            char('a')->followedBy(char('b'))
                ->map('strtoupper');

        $expected = "B";

        $this->assertParses("abca", $parser, $expected);
    }

    /** @test */
    public function simple_eur()
    {
        $parser = sequence(
            char('€'),
            float()->map(fn($v)=>new SimpleEur((float) $v))
        );
        $this->assertParses("€1.25", $parser, new SimpleEur(1.25));

    }
}

class MyType1
{
    private $val;

    public function __construct($val)
    {
        $this->val = $val;
    }
}


final class SimpleEur
{
    private float $val;

    public function __construct(float $val)
    {
        $this->val = $val;
    }

}
