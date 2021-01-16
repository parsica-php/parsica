<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\ParseResult;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Internal\Position;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\char;
use function Verraes\Parsica\many;
use function Verraes\Parsica\newline;
use function Verraes\Parsica\repeat;
use function Verraes\Parsica\skipSpace;
use function Verraes\Parsica\string;
use function Verraes\Parsica\whitespace;

final class ErrorReportingTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function failing_on_the_first_token()
    {
        $parser = char('x');
        $input = StringStream::fromString("abc");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:1
              |
            1 | abc
              | ^— column 1
            Unexpected 'a'
            Expecting 'x'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }


    /** @test */
    public function works_for_parsers_with_more_than_one_character()
    {
        $parser = string("abc");
        $input = StringStream::fromString("xyz", "/path/to/file");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            /path/to/file:1:1
              |
            1 | xyz
              | ^— column 1
            Unexpected 'x'
            Expecting 'abc'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function advance_the_column_with_sequence()
    {
        $parser = char('a')->sequence(char('b'));
        $input = StringStream::fromString("axy");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:2
              |
            1 | ...xy
              |    ^— column 2
            Unexpected 'x'
            Expecting 'b'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function works_with_custom_labels()
    {
        $parser = char('a')->sequence(char('b'))->label("a followed by b");
        $input = StringStream::fromString("axy");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:2
              |
            1 | ...xy
              |    ^— column 2
            Unexpected 'x'
            Expecting a followed by b
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function tabs_move_column_position()
    {
        $parser = skipSpace()->sequence(char('a'));
        $input = StringStream::fromString("\t\tbcdefgh");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:9
              |
            1 | ...bcdefgh
              |    ^— column 9
            Unexpected 'b'
            Expecting 'a'
            ERROR;
        $this->assertEquals($expected, $result->errorMessage());
    }


    /** @test */
    public function line_numbers_space_out()
    {
        $parser = skipSpace()->sequence(char('a'));
        $input = StringStream::fromString(str_repeat("\n", 99) . "b");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:100:1
                |
            100 | b
                | ^— column 1
            Unexpected 'b'
            Expecting 'a'
            ERROR;
        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function multiline_input()
    {
        $parser = many(newline())->sequence(char('a'));
        $input = StringStream::fromString("\n\n\nbcd\nxyz", Position::initial("/path/to/file"));
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            /path/to/file:4:1
              |
            4 | bcd
              | ^— column 1
            Unexpected 'b'
            Expecting 'a'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }


    /** @test */
    public function indicate_position()
    {
        $parser = repeat(5, char('a'))->sequence(char('b'));
        $input = StringStream::fromString("aaaaaXYZ");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:6
              |
            1 | ...XYZ
              |    ^— column 6
            Unexpected 'X'
            Expecting 'b'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function repeatN()
    {
        $parser = repeat(5, char('a'))->sequence(char('b'));
        $input = StringStream::fromString("aaaaXYZ");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:5
              |
            1 | ...XYZ
              |    ^— column 5
            Unexpected 'X'
            Expecting 5 times 'a'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function indicate_shorter_position()
    {
        $parser = string("aa")->sequence(char('b'));
        $input = StringStream::fromString("aaXYZ");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:3
              |
            1 | ...XYZ
              |    ^— column 3
            Unexpected 'X'
            Expecting 'b'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }


    /** @test */
    public function truncate_long_lines()
    {
        $parser = skipSpace()->sequence(string("Hello"))->sequence(char(','))->sequence(whitespace())->sequence(string("World"));
        $input = StringStream::fromString("\n\n\n\n\n\n\n\n\nHello World! This is a really long line of more than 80 characters, if you count the spaces.");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:10:6
               |
            10 | ... World! This is a really long line of more than 80 characters, if you...
               |    ^— column 6
            Unexpected <space>
            Expecting ','
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function dont_truncate_short_enough_lines()
    {
        $parser = char('a');
        $input = StringStream::fromString("1234567890123456789012345678901234567890123456789012345678901234567890123456");
        $result = $parser->run($input);
        $expected =
            <<<ERROR
            <input>:1:1
              |
            1 | 1234567890123456789012345678901234567890123456789012345678901234567890123456
              | ^— column 1
            Unexpected '1'
            Expecting 'a'
            ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

}
