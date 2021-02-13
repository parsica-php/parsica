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

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\MBStringStream;
use function Verraes\Parsica\any;
use function Verraes\Parsica\char;
use function Verraes\Parsica\fail;
use function Verraes\Parsica\string;

final class LabelTest extends TestCase
{
    /** @test */
    public function or_label()
    {
        $parser = char('a')->or(char('b'));
        $input = "c";
        $result = $parser->run(new MBStringStream($input));
        $this->assertEquals("'a' or 'b'", $result->expected());
    }

    /** @test */
    public function or_label2()
    {
        $parser = string('hello')->or(string('world'));
        $input = "foo";
        $result = $parser->run(new MBStringStream($input));
        $this->assertEquals("'hello' or 'world'", $result->expected());
    }

    /** @test */
    public function any_label()
    {
        $parser = any(char('a'), char('b'), string("hello"));
        $input = "foo";
        $result = $parser->run(new MBStringStream($input));
        $this->assertEquals("'a' or 'b' or 'hello'", $result->expected());
    }

    /** @test */
    public function failure_label()
    {
        $parser = fail("reason");
        $result = $parser->run(new MBStringStream("foo"));
        $this->assertEquals("reason", $result->expected());
    }
}
