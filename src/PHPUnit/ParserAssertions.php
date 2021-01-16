<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\PHPUnit;

use Exception;
use Verraes\Parsica\Parser;
use Verraes\Parsica\StringStream;

/**
 * Convenience assertion methods. When writing tests for your own parsers, extend from this instead of PHPUnit's TestCase.
 *
 * @TODO move to standalone package
 * @api
 */
trait ParserAssertions
{
    /**
     * @psalm-param mixed $expectedOutput
     *
     * @api
     */
    protected function assertParses(string $input, Parser $parser, $expectedOutput, string $message = ""): void
    {
        $stream = StringStream::fromString($input);
        $actualResult = $parser->run($stream);
        if ($actualResult->isSuccess()) {
            $this->assertEquals(
                $expectedOutput,
                $actualResult->output(),
                $message . "\n" . "The parser succeeded but the output doesn't match your expected output."
            );
        } else {
            $this->fail(
                $message . "\n"
                ."The parser failed with the following error message:\n"
                .$actualResult->errorMessage()."\n"
            );
        }
    }

    abstract public static function assertSame($expected, $actual, string $message = ''): void;

    abstract public static function assertEquals($expected, $actual, string $message = ''): void;

    abstract public static function fail(string $message = ''): void;

    /**
     * @param string $input
     * @param Parser $parser
     * @param string $expectedRemaining
     * @param string $message
     *
     * @api
     */
    protected function assertRemainder(string $input, Parser $parser, string $expectedRemaining, string $message = ""): void
    {
        $stream = StringStream::fromString($input);
        $actualResult = $parser->run($stream);
        if ($actualResult->isSuccess()) {
            $this->assertEquals(
                $expectedRemaining,
                $actualResult->remainder()->peakN(80),
                $message . "\n" . "The parser succeeded but the expected remaining input doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n"
                . "The parser failed with the following error message:\n"
                .$actualResult->errorMessage()."\n"
            );
        }
    }

    /**
     * @param string      $input
     * @param Parser      $parser
     * @param string|null $expectedFailure
     * @param string      $message
     *
     * @api
     */
    protected function assertParseFails(string $input, Parser $parser, ?string $expectedFailure = null, string $message = ""): void
    {
        $input = StringStream::fromString($input);
        $actualResult = $parser->run($input);
        $this->assertTrue(
            $actualResult->isFail(),
            $message . "\n" . "The parser succeeded but expected a failure."
        );

        if (isset($expectedFailure)) {
            $this->assertEquals(
                $expectedFailure,
                $actualResult->expected(),
                $message . "\n" . "The expected failure message is not the same as the actual one."
            );
        }
    }

    abstract public static function assertTrue($condition, string $message = ''): void;

    /**
     * @api
     */
    protected function assertFailOnEOF(Parser $parser, string $message = ""): void
    {
        $actualResult = $parser->run(StringStream::fromString(""));
        $this->assertTrue(
            $actualResult->isFail(),
            $message . "\n" . "Expected the parser to fail on EOL."
        );
    }

    /**
     * @api
     */
    protected function assertSucceedOnEOF(Parser $parser, string $message = ""): void
    {
        $actualResult = $parser->run(StringStream::fromString(""));
        $this->assertTrue(
            $actualResult->isSuccess(),
            $message . "\n" . "Expected the parser to succeed on EOL."
        );
        $this->assertSame("", $actualResult->output());
    }
}
