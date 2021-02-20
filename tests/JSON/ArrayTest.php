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
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\JSON\key_value;

final class ArrayTest extends TestCase
{
    use ParserAssertions;


    /**
     * @test
     * @dataProvider examples
     */
    public function array(string $input, $expected)
    {
        $parser = JSON::array();
        $this->assertParses($input, $parser, $expected);
    }

    public function examples()
    {
        return [
            ['[]', []],
            ['[ ] ', []],
            ['[ 1 ] ', [1.0]],
            ['[ true ] ', [true]],
            ['[ 1.23, "abc", null, false ] ', [ 1.23, "abc", null, false]],
        ];
    }


}

