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
        $result = $parser($input);
        if ($result->isSuccess()) {
            $this->assertEquals(
                $expectedParsed,
                $result->parsed(),
                $message . "\n" . "The parser succeeded but the parsed value doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n" .
                "Parser test failed."
                . "\nInput: $input"
                . "\nTest expected: " . $expectedParsed
                . "\nParser expected: " . $result->expected()
                . "\nGot: " . $result->got()
            );
        }
    }

    protected function assertRemain(string $expectedRemaining, Parser $parser, string $input, string $message = "") : void
    {
        $actual = $parser($input);
        if ($actual->isSuccess()) {
            $this->assertEquals(
                $expectedRemaining,
                $actual->remaining(),
                $message . "\n" . "The parser succeeded but the expected remaining input doesn't match."
            );
        } else {
            $this->fail(
                $message . "\n" .
                "Parser test failed."
                . "\nInput: $input"
                . "\nExpected remaining: " . var_Export($expectedRemaining, true)
            );
        }
    }

    protected function assertNotParse(Parser $parser, string $input, ?string $expectedFailure = null, string $message = "") : void
    {
        $actual = $parser($input);
        $this->assertFalse(
            $actual->isSuccess(),
            $message . "\n" . "Parser succeeded but expected a failure.\nInput: $input"
        );

        if (isset($expectedFailure)) {
            $this->assertEquals(
                $expectedFailure,
                $actual->expected(),
                $message . "\n" . "The expected failure message is not the same as the actual one."
            );
        }
    }
}
