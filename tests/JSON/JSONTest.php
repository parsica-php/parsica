<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\JSON;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\JSON\JSON;

final class JSONTest extends TestCase
{
    public static function examples(): array
    {
        return [
            ['true'],
            ['false'],
            ['null'],
            ['"abc"'],
            ['{"a b":"c d"}'],
            [' { " a b  " : " c  d " } '],
            [' [ { " a b  " : " c  d " } ] '],
            [' [ { " a b  " : " c  d " } , { "ef" : "gh" } ] '],
            ['"some weird chars \\n in \\t strings \\u9999 should do it"'],
            ['"this \\\\ is just a backslash"'],
            [<<<JSON
                [
                    -1.23,
                    null,
                    true,
                    [
                        [
                            {
                                "a": true
                            },
                            {
                                "b": false,
                                "c": -1.23456789E+123
                            }
                        ]
                    ]
                ]
                JSON,
            ],
            [file_get_contents(__DIR__ . '/../../composer.json')],
        ];
    }

    /**
     * @test
     * @dataProvider examples
     */
    public function compare_to_json_decode(string $input)
    {
        $native = json_decode($input);
        $parsica = JSON::json()->tryString($input)->output();
        $this->assertEquals($native, $parsica);
    }
}
