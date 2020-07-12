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
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\JSON\stringLiteral;

final class StringLiteralTest extends TestCase
{
    use ParserAssertions;

    public static function escapedChars(): array
    {
        return [
            // label => [literal that will appear in json, expected character it results in]
            "quotation mark" => ["\\\"", '"'],
            "reverse solidus" => ['\\\\', '\\'],
            "solidus" => ["\\/", '/'],
            "backspace" => ["\\b", mb_chr(8)],
            "formfeed" => ["\\f", mb_chr(12)],
            "linefeed" => ["\\n", "\n"],
            "carriage return" => ["\\r", "\r"],
            "horizontal tab" => ["\\t", "\t"],
        ];
    }

    /** @test */
    public function empty()
    {
        $this->assertParse("", stringLiteral(), '""');
    }

    /**
     * @test
     * @dataProvider escapedChars
     */
    public function escapes(string $input, string $expected)
    {
        $this->assertParse($expected, stringLiteral(), '"' . $input . '"');
        $this->assertParse("a" . $expected, stringLiteral(), '"a' . $input . '"');
        $this->assertParse($expected . "a", stringLiteral(), '"' . $input . 'a"');
    }

    /** @test */
    public function escape_hex()
    {
        $input = '"\\u0BB9\\u0BB2\\u0BCB\\u0020\\u0B89\\u0BB2\\u0B95\\u0BAE\\u0BCD"';
        $this->assertParse("ஹலோ உலகம்", stringLiteral(), $input);
    }


}
