<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use PHPUnit\Framework\TestCase;

abstract class ParserTest extends TestCase
{
    protected function shouldParse(Parser $parser, string $input, $expectedParsed, $expectedRemaining = null)
    {
        $actual = $parser($input);
        if ($actual->isSuccess()) {
            $this->assertEquals($expectedParsed, $actual->parsed());
            if ($expectedRemaining) {
                $this->assertEquals($expectedRemaining, $actual->remaining());
            }
        } else {
            $this->fail(
                "Parser test failed."
                . "\nInput: $input"
                . "\nExpected: $expectedParsed"
                . "\nParser expected: {$actual->expectation()}"
            );
        }
    }

    protected function shouldNotParse(Parser $parser, string $input, ?string $expectedFailure = null)
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
