<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\PHPUnit;

use Mathias\ParserCombinator\Parser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Convenience assertion methods. When writing tests for your own parsers, extend from this instead of PHPUnit's TestCase.
 *
 */
abstract class ParserTestCase extends TestCase
{
    /**
     * @see \Tests\Mathias\ParserCombinator\PHPUnit\ParserTestCaseTest::strict_equality
     */
    protected function assertStrictlyEquals($expected, $actual, string $message = ''): void
    {
        // Just a POC implementation.
        if(is_scalar($expected) ) {
            $this->assertSame($expected, $actual, $message);
        } elseif(is_object($expected)) {
            $this->assertEquals(get_class($expected), get_class($actual),
                "Expected type didn't match actual type");
            $this->assertEquals($expected, $actual, $message);
        } else {
            throw new \Exception("@todo Not implemented");
        }
    }

    /**
     * @param mixed $expectedParsed
     */
    protected function assertParse($expectedParsed, Parser $parser, string $input, string $message = ""): void
    {
        $actualResult = $parser->run($input);
        if ($actualResult->isSuccess()) {
            $this->assertStrictlyEquals(
                $expectedParsed,
                $actualResult->parsed(),
                $message . "\n" . "The parser succeeded but the parsed value doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n" .
                "Parser test failed."
                . "\nInput: $input"
                . "\nTest expected: " . (string)$expectedParsed
                . "\nParser expected: " . $actualResult->expected()
                . "\nGot: " . $actualResult->got()
            );
        }
    }

    protected function assertRemain(string $expectedRemaining, Parser $parser, string $input, string $message = ""): void
    {
        $actualResult = $parser->run($input);
        if ($actualResult->isSuccess()) {
            $this->assertEquals(
                $expectedRemaining,
                $actualResult->remaining(),
                $message . "\n" . "The parser succeeded but the expected remaining input doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n" .
                "Parser failed."
                . "\nInput: $input"
                . "\nExpected remaining: " . var_Export($expectedRemaining, true)
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

    protected function assertFailOnEOF(Parser $parser, string $message = "") : void
    {
        $actualResult = $parser->run("");
        $this->assertTrue(
            $actualResult->isFail(),
            $message . "\n" . "Expected the parser to fail on EOL."
        );
    }

    protected function assertSucceedOnEOF(Parser $parser, string $message = "") : void
    {
        $actualResult = $parser->run("");
        $this->assertTrue(
            $actualResult->isSuccess(),
            $message . "\n" . "Expected the parser to succeed on EOL."
        );
        $this->assertSame("", $actualResult->parsed());
    }
}
