<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use Mathias\ParserCombinator\Parser;
use PHPUnit\Framework\TestCase;

abstract class ParserTest extends TestCase
{
    protected function assertParse(Parser $parser, string $input, $expectedParsed)
    {
        $result = $parser($input);
        if ($result->isSuccess()) {
            $this->assertEquals(
                $expectedParsed,
                $result->parsed(),
                "The parser succeeded but the parsed value doesn't match."
            );
        } else {
            $this->fail(
                "Parser test failed."
                . "\nInput: $input"
                . "\nTest expected: ".$expectedParsed
                . "\nParser expected: ".$result->expected()
                . "\nGot: ".$result->got()
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
                $actual->expected(),
                "The expected failure message is not the same as the actual one."
            );
        }
    }
}
