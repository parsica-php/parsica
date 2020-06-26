<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_3_0\Parser;

use Exception;
use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\char;

final class AppendTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function append_strings()
    {
        $parser = char('a')->append(char('b'));
        $this->assertParse("ab", $parser, "abc");
    }

    /** @test */
    public function append_array()
    {
        $a = char('a')->map(fn($x) => [$x]);
        $b = char('b')->map(fn($x) => [$x]);
        $this->assertParse(['a', 'b'], $a->append($b), "abc");
    }


    /** @test */
    public function append_non_semigroup()
    {
        $a = char('a')->construct(NotASemigroup::class);
        $b = char('b')->construct(NotASemigroup::class);
        $this->expectException(Exception::class);
        $a->append($b)->run('abc');
    }
}

final class NotASemigroup
{

    public function __construct($_)
    {
    }
}
