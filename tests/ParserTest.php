<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use Mathias\ParserCombinator\Parser;
use PHPUnit\Framework\TestCase;

abstract class ParserTest extends TestCase
{
    protected function assertParse(Parser $parser, string $input, $expectedParsed)
    {
        $actual = $parser($input);
        if ($actual->isSuccess()) {
            $this->assertEquals(
                $expectedParsed,
                $actual->parsed(),
                "The parser succeeded but the parsed value doesn't match."
            );
        } else {
            $this->fail(
                "Parser test failed."
                . "\nInput: $input"
                . "\nExpected: ".var_Export($expectedParsed, true)
                . "\nParser expected: {$actual->expectation()}"
            );
        }
    }

    protected function assertRemain(Parser $parser, string $input, $expectedRemaining)
    {
        $actual = $parser($input);
        if ($actual->isSuccess()) {
            $this->assertEquals(
                $expectedRemaining,
                $actual->remaining(),
                "The parser succeeded but the expected remaining input doesn't match."
            );
        } else {
            $this->fail(
                "Parser test failed."
                . "\nInput: $input"
                . "\nExpected remaining: ".var_Export($expectedRemaining, true)
            );
        }
    }

    protected function assertNotParse(Parser $parser, string $input, ?string $expectedFailure = null)
    {
        $actual = $parser($input);
        $this->assertFalse(
            $actual->isSuccess(),
            "Parser succeeded but expected a failure.\nInput: $input"
        );

        if (isset($expectedFailure)) {
            $this->assertEquals(
                $expectedFailure,
                $actual->expectation(),
                "The expected failure message is not the same as the actual one."
            );
        }
    }
}
