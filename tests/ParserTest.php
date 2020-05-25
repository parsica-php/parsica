<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use PHPUnit\Framework\TestCase;

abstract class ParserTest extends TestCase
{
    protected function shouldParse(Parser $parser, string $input, $expected)
    {
        $actual = $parser($input);
        if ($actual->isSuccess()) {
            $this->assertEquals($expected, $actual->parsed());
        } else {
            $this->fail("Parser test failed.\nInput: $input\nExpected: $expected\nMessage: " . $actual->expectation());
        }
    }

    protected function shouldNotParse(Parser $parser, string $input, ?string $expectedFailure = null)
    {
        $actual = $parser($input);
        if ($actual->isSuccess()) {
            $this->fail("Parser succeeded but expected a failure.\nInput: $input");
        } else {
            if (isset($expectedFailure)) {
                $this->assertEquals($expectedFailure, $actual->expectation(), "The expected failure message is not the same as the actual one.");
            }
        }
    }

}
