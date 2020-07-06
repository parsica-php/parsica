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
     * @psalm-param mixed $expectedParsed
     *
     * @api
     */
    protected function assertParse($expectedParsed, Parser $parser, string $input, string $message = ""): void
    {
        $input = new StringStream($input);
        $actualResult = $parser->run($input);
        if ($actualResult->isSuccess()) {
            $this->assertStrictlyEquals(
                $expectedParsed,
                $actualResult->output(),
                $message . "\n" . "The parser succeeded but the output doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n" .
                "Parser failed."
                . "\nInput: $input"
                . "\nTest expected: " . print_r($expectedParsed, true)
                . "\nParser expected: " . $actualResult->expected()
                . "\nParser got: " . $actualResult->got()
            );
        }
    }

    /**
     * @psalm-param mixed  $expected
     * @psalm-param mixed  $actual
     * @psalm-param string $message
     *
     * @throws Exception
     * @api
     * @see \Tests\Verraes\Parsica\v0_3_0\PHPUnit\ParserTestCaseTest::strict_equality
     *
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAccess
     */
    protected function assertStrictlyEquals($expected, $actual, string $message = ''): void
    {
        // Just a POC implementation.
        if (is_null($expected) || is_scalar($expected)) {
            $this->assertSame($expected, $actual, $message);
        } elseif (is_object($expected)) {
            $this->assertEquals(get_class($expected), get_class($actual),
                "Expected type didn't match actual type");
            $this->assertEquals($expected, $actual, $message);
        } elseif (is_array($expected)) {
            $this->assertSame(count($expected), count($actual), "The length of the  actual array differs from the length of the expected array.");
            foreach ($expected as $k => $v) {
                $this->assertStrictlyEquals($expected[$k], $actual[$k], "Item $k from the actual array differs from item $k in the expected array");
            }
        } else {
            throw new Exception("@todo Not implemented");
        }
    }

    abstract public static function assertSame($expected, $actual, string $message = ''): void;

    abstract public static function assertEquals($expected, $actual, string $message = ''): void;

    abstract public static function fail(string $message = ''): void;

    /**
     * @api
     */
    protected function assertRemain(string $expectedRemaining, Parser $parser, string $input, string $message = ""): void
    {
        $input = new StringStream($input);
        $actualResult = $parser->run($input);
        if ($actualResult->isSuccess()) {
            $this->assertEquals(
                $expectedRemaining,
                $actualResult->remainder(),
                $message . "\n" . "The parser succeeded but the expected remaining input doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n" .
                "Parser failed, and a failing parser doesn't have a remainder."
                . "\nInput: $input"
                . "\nExpected remaining: " . var_export($expectedRemaining, true)
                . "\nParser expected: " . $actualResult->expected()
                . "\nGot: " . $actualResult->got()
            );
        }
    }

    /**
     * @api
     */
    protected function assertNotParse(Parser $parser, string $input, ?string $expectedFailure = null, string $message = ""): void
    {
        $input = new StringStream($input);
        $actualResult = $parser->run($input);
        $this->assertTrue(
            $actualResult->isFail(),
            $message . "\n" . "Parser succeeded but expected a failure.\nInput: $input"
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
        $actualResult = $parser->run(new StringStream(""));
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
        $actualResult = $parser->run(new StringStream(""));
        $this->assertTrue(
            $actualResult->isSuccess(),
            $message . "\n" . "Expected the parser to succeed on EOL."
        );
        $this->assertSame("", $actualResult->output());
    }
}
