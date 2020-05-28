<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\PHPUnit;

use Mathias\ParserCombinator\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Convenience assertion methods. When writing tests for your own parsers, extend from this instead of PHPUnit's TestCase.
 */
abstract class ParserTestCase extends TestCase
{
    /**
     * @param mixed $expectedParsed
     */
    protected function assertParse($expectedParsed, Parser $parser, string $input, string $message = ""): void
    {
        $actualResult = $parser->run($input);
        if ($actualResult->isSuccess()) {
            $this->assertEquals(
                $expectedParsed,
                $actualResult->parsed(),
                $message . "\n" . "The parser succeeded but the parsed value doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n" .
                "Parser test failed."
                . "\nInput: $input"
                . "\nTest expected: " . $expectedParsed
                . "\nParser expected: " . $actualResult->expected()
                . "\nGot: " . $actualResult->got()
            );
        }
    }

    protected function assertRemain(string $expectedRemaining, Parser $parser, string $input, string $message = "") : void
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

    protected function assertNotParse(Parser $parser, string $input, ?string $expectedFailure = null, string $message = "") : void
    {
        $actualResult = $parser->run($input);
        $this->assertFalse(
            $actualResult->isSuccess(),
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
}
