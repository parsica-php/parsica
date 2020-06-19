<?php declare(strict_types=1);

namespace Verraes\Parsica\PHPUnit;

use Exception;
use Verraes\Parsica\Parser;

/**
 * Convenience assertion methods. When writing tests for your own parsers, extend from this instead of PHPUnit's TestCase.
 *
 * @TODO move to standalone package
 */
trait ParserAssertions
{
    /**
     * @param mixed $expectedParsed
     */
    protected function assertParse($expectedParsed, Parser $parser, string $input, string $message = ""): void
    {
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
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     *
     * @throws Exception
     * @see \Tests\Verraes\Parsica\PHPUnit\ParserTestCaseTest::strict_equality
     *
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAccess
     */
    protected function assertStrictlyEquals($expected, $actual, string $message = ''): void
    {
        // Just a POC implementation.
        if (is_scalar($expected)) {
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

    protected function assertRemain(string $expectedRemaining, Parser $parser, string $input, string $message = ""): void
    {
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
                "Parser failed."
                . "\nInput: $input"
                . "\nExpected remaining: " . var_export($expectedRemaining, true)
                . "\nParser expected: " . $actualResult->expected()
                . "\nGot: " . $actualResult->got()
            );
        }
    }

    protected function assertNotParse(Parser $parser, string $input, ?string $expectedFailure = null, string $message = ""): void
    {
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

    protected function assertFailOnEOF(Parser $parser, string $message = ""): void
    {
        $actualResult = $parser->run("");
        $this->assertTrue(
            $actualResult->isFail(),
            $message . "\n" . "Expected the parser to fail on EOL."
        );
    }

    protected function assertSucceedOnEOF(Parser $parser, string $message = ""): void
    {
        $actualResult = $parser->run("");
        $this->assertTrue(
            $actualResult->isSuccess(),
            $message . "\n" . "Expected the parser to succeed on EOL."
        );
        $this->assertSame("", $actualResult->output());
    }
}
