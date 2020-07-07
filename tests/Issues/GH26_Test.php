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
use function Verraes\Parsica\any;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;
use function Verraes\Parsica\either;
use function Verraes\Parsica\emit;
use function Verraes\Parsica\eof;
use function Verraes\Parsica\failure;
use function Verraes\Parsica\many;
use function Verraes\Parsica\success;

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

    /**
     * https://github.com/mathiasverraes/parsica/issues/6#issuecomment-653772920
     *
     * @test
     */
    public function only_the_first_successful_parser_in_an_either_should_call_emit()
    {
        $x = new class {
            public $first = false;
            public $second = false;
        };

        $parser = either(
            emit(
                success(),
                function ($output) use ($x) {
                    $x->first = true; // is called
                }
            ),
            emit(
                success(),
                function ($output) use ($x) {
                    $x->second = true; // is not called
                }
            )
        );
        $result = $parser->tryString('test');

        $this->assertEquals(true, $x->first);
        $this->assertEquals(false, $x->second, "Either should only call emit on the first successful parser");

    }

    /**
     * @TODO Set $repeatParser at 500 and fix the performance issues.
     *
     * https://github.com/mathiasverraes/parsica/issues/6#issuecomment-653772920
     *
     * @test
     */
    public function it_should_parse_500_times_in_under_100_ms()
    {
        // Number of times we run the parser
        $repeatParser =  1;//500;

        $propertyName = atLeastOne(alphaNumChar());

        $type = emit(
            either(
                eof(),
                char('@')
                    ->followedBy($propertyName)
                    ->thenIgnore(eof()),
            ),
            function () {}
        );

        $map = emit(
            char('.')->followedBy($propertyName),
            function () {}
        );

        $list = emit(
            between(
                char('['),
                char(']'),
                either(
                    char('@')
                        ->followedBy($propertyName)
                        ->map(fn($value) => [
                            'discriminatorName' => $value,
                            'keepKeys'          => true
                        ]),
                    $propertyName
                        ->map(fn($value) => [
                            'discriminatorName' => $value,
                            'keepKeys'          => false
                        ]),
                )
            ),
            function () {}
        );

        $root = emit(
            char('$'),
            function () {}
        );

        $rest = many(any($map, $list))->followedBy($type);

        $parser = either(
            failure("message"), // $context->preflightCacheParser(),
            $root
        )->followedBy($rest);

        $start = microtime(true);
        for ($i = 0; $i < $repeatParser; $i++) {
            $parser->tryString('$.q.w[@1].e[2]@int');
        }
        $end = microtime(true);

        $this->assertLessThan(0.1, $end - $start);
    }

}