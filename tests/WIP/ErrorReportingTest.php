<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\WIP;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Internal\Position;
use Verraes\Parsica\Internal\StringStream;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\char;
use function Verraes\Parsica\many;
use function Verraes\Parsica\newline;
use function Verraes\Parsica\skipSpace;
use function Verraes\Parsica\string;

final class ErrorReportingTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function failing_on_the_first_token()
    {
        $parser = char('a');
        $input = new StringStream("bcd");
        $result = $parser->run($input);
        $expected = <<<ERROR
<input>:1:1
  |
1 | bcd
  | ^
Unexpected 'b'
Expecting 'a'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function failing_with_an_advanced_position()
    {
        $parser = char('a');
        $input = new StringStream("bcd", new Position("/path/to/file", 5, 10));
        $result = $parser->run($input);
        $expected = <<<ERROR
/path/to/file:5:10
  |
5 | bcd
  | ^
Unexpected 'b'
Expecting 'a'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function works_for_parsers_with_more_than_one_character()
    {
        $parser = string("abc");
        $input = new StringStream("xyz", Position::initial("/path/to/file"));
        $result = $parser->run($input);
        $expected = <<<ERROR
/path/to/file:1:1
  |
1 | xyz
  | ^
Unexpected 'x'
Expecting 'abc'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function advance_the_column_with_followedBy()
    {
        $parser = char('a')->sequence(char('b'));
        $input = new StringStream("axy");
        $result = $parser->run($input);
        $expected = <<<ERROR
<input>:1:2
  |
1 | xy
  | ^
Unexpected 'x'
Expecting 'b'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function works_with_custom_labels()
    {
        $parser = char('a')->sequence(char('b'))->label("a followed by b");
        $input = new StringStream("axy");
        $result = $parser->run($input);
        $expected = <<<ERROR
<input>:1:2
  |
1 | xy
  | ^
Unexpected 'x'
Expecting a followed by b

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function tabs_move_column_position()
    {
        $parser = skipSpace()->sequence(char('a'));
        $input = new StringStream("\t\tb");
        $result = $parser->run($input);
        $expected = <<<ERROR
<input>:1:9
  |
1 | b
  | ^
Unexpected 'b'
Expecting 'a'

ERROR;
        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function WISHFUL_THINKING_indicate_position_in_error_messages_inside_a_line()
    {
        $parser = char('a')->followedBy(char('b'));
        $input = new StringStream("acd", Position::initial("/path/to/file"));
        $result = $parser->run($input);
        $expected = <<<ERROR
/path/to/file:1:1
  |
1 | acd
  |  ^
Unexpected 'c'
Expecting 'b'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function multiline_input()
    {
        $parser = many(newline())->sequence(char('a'));
        $input = new StringStream("\n\n\nbcd\nxyz", Position::initial("/path/to/file"));
        $result = $parser->run($input);
        $expected = <<<ERROR
/path/to/file:1:1
  |
4 | bcd
  | ^
Unexpected 'b'
Expecting 'a'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

    /** @test */
    public function indicate_unexpected_control_char()
    {
        $parser = char('a');
        $input = new StringStream("\n", Position::initial("/path/to/file"));
        $result = $parser->run($input);
        $expected = <<<ERROR
/path/to/file:1:1
  |
1 |
  | ^
Unexpected '<line feed>'
Expecting 'a'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }

}
