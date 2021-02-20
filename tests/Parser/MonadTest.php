<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\Parser;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\anySingle;
use function Parsica\Parsica\bind;
use function Parsica\Parsica\char;
use function Parsica\Parsica\pure;
use function Parsica\Parsica\sequence;

final class MonadTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function bind()
    {
        // This parser checks if the second character is the same as the first, by taking the output of the first
        // parser and binding it to a function that produces the second parser from that output.
        $parser = anySingle()->bind(fn(string $c) => char($c));
        $this->assertParses("aa", $parser, "a");
        $this->assertParses("bb", $parser, "b");
        $this->assertParseFails("ab", $parser);

        $parser = bind(anySingle(), fn(string $c) => char($c));
        $this->assertParses("aa", $parser, "a");
        $this->assertParses("bb", $parser, "b");
        $this->assertParseFails("ab", $parser);
    }

    /** @test */
    public function bind_fails()
    {
        // If the first parser fails, bind() returns the first one.
        $parser = char('x')->bind(fn(string $c) => char($c));
        $this->assertParses("xx", $parser, "x");
        $this->assertParseFails("yx", $parser);
    }

    /** @test */
    public function sequence()
    {
        $parser = char('a')->sequence(char('b'));
        $this->assertParses("ab", $parser, "b");
        $this->assertParseFails("aa", $parser);

        $parser = sequence(char('a'), char('b'));
        $this->assertParses("ab", $parser, "b");
        $this->assertParseFails("aa", $parser);
    }

    /** @test */
    public function sequence_error_should_show_the_label_of_the_failing_parser()
    {
        $parser = char('a')->sequence(char('b'));
        $this->assertParseFails("X", $parser, "'a'");
        $this->assertParseFails("aX", $parser, "'b'");

    }

    /** @test */
    public function pure()
    {
        $parser = pure("hi");
        $this->assertParses("something else", $parser, "hi");

    }


}
