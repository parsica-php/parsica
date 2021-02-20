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
use function Parsica\Parsica\assemble;
use function Parsica\Parsica\char;
use function Parsica\Parsica\string;

final class assembleTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function assemble_string()
    {
        $parser = assemble(
            string('first'),
            string('second'),
        );
        $this->assertParses("firstsecond", $parser, "firstsecond");
        $this->assertRemainder("firstsecond", $parser, "");
    }

    /** @test */
    public function assemble_string_ignore()
    {
        $parser = assemble(
            string('first')->thenIgnore(char('-')),
            string('second'),
        );
        $this->assertParses("first-second", $parser, "firstsecond");
        $this->assertRemainder("first-second", $parser, "");
    }

    /** @test */
    public function assemble_arrays()
    {
        $toArray = fn($v) => [$v];

        $parser = assemble(
            string('first')->map($toArray),
            string('second')->map($toArray),
        );

        $input = "firstsecond";
        $expected = ["first", "second"];
        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function assemble_different_types_but_the_others_are_ignored()
    {
        // @todo this could be more elegant
        $toArray = fn($v) => [$v];
        $parser = assemble(
            char('[')->sequence(
                string('first')->map($toArray)
            ),
            char(',')->sequence(
                string('second')->map($toArray)
            )->thenIgnore(char(']')),
        );

        $input = "[first,second]";
        $expected = ["first", "second"];

        $this->assertParses($input, $parser, $expected);
    }

    /** @test */
    public function label()
    {
        $parser = assemble(
            string('first'),
            string('second'),
        );
        $this->assertParseFails("X", $parser, "'first'");
        $this->assertParseFails("firsX", $parser, "'first'");
        $this->assertParseFails("firstX", $parser, "'second'");

    }

}
