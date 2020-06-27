<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\Issues;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\alphaNumChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\char;
use function Verraes\Parsica\many;

/**
 * https://github.com/mathiasverraes/parsica/issues/6
 */
final class GH26_Test extends TestCase
{
    use ParserAssertions;

    private static function pathParser(): Parser
    {
        $sep = char('/')
            ->label('directory separator');
        // unix supports other characters, such as space, so adapt if needed
        $name = atLeastOne(char('.')->or(char('_'))->or(alphaNumChar()))
            ->label("directory or filename");
        $parser = many($sep->followedBy($name));
        return $parser;
    }

    /** @test */
    public function parsing_a_simple_path()
    {
        $parser = self::pathParser();

        $input = "/a/b/c/file1";
        $expected = ["a", "b", "c", "file1"];

        $this->assertParse($expected, $parser, $input);
    }
}
