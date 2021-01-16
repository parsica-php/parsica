<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Parser;

use Exception;
use PHPUnit\Framework\TestCase;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\char;

final class AppendTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function append_strings()
    {
        $parser = char('a')->append(char('b'));
        $this->assertParses("abc", $parser, "ab");
    }

    /** @test */
    public function append_array()
    {
        $a = char('a')->map(fn($x) => [$x]);
        $b = char('b')->map(fn($x) => [$x]);
        $this->assertParses("abc", $a->append($b), ['a', 'b']);
    }


    /** @test */
    public function append_non_semigroup()
    {
        $a = char('a')->map(fn($v)=> new NotASemigroup($v));
        $b = char('b')->map(fn($v)=> new NotASemigroup($v));
        $this->expectException(Exception::class);
        $a->append($b)->run(StringStream::fromString('abc'));
    }
}

final class NotASemigroup
{

    public function __construct($_)
    {
    }
}
