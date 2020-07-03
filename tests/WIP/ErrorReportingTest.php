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

final class ErrorReportingTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function indicate_position_in_error_messages()
    {
        $parser = char('a');
        $input = new StringStream("bcd", Position::initial("/path/to/file"));
        $result = $parser->run($input);
        $expected = <<<ERROR
/path/to/file:1:1
  |
1 | bcd
  | ^
Unexpected 'b'
Expecting 'a'

ERROR;

        $this->assertEquals($expected, $result->errorMessage());
    }


}
