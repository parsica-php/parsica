<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\JSON;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\JSON\JSON;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\JSON\key_value;

final class ObjectTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function member()
    {
        $input = '"foo":"bar"';
        $parser = JSON::member();
        $this->assertParses($input, $parser, ["foo", "bar"]);
    }

    /** @test */
    public function object()
    {
        $input = '{"foo":"bar","bar":"foo"}';
        $parser = JSON::object();
        $this->assertParses($input, $parser, (object)["foo" => "bar", "bar" => "foo"]);
    }



}

