<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Tests;
require_once __DIR__ . '/../vendor/autoload.php';

// @TODO fix, this should be autoloaded
require_once __DIR__.'/../src/Functions.php';

use Mathias\ParserCombinators\Result;
use PHPUnit\Framework\TestCase;
use function Mathias\ParserCombinators\{either, char};

abstract class ParserTest extends TestCase
{
    protected function shouldParse($parser, string $input, $expected)
    {
        /** @var Result $actual */
        $actual = $parser($input);
        if ($actual->isSuccess()) {
            $this->assertEquals($expected, $actual->parsed());
        } else {
            $this->fail("Parser test failed.\nInput: $input\nExpected: $expected\nMessage: " . $actual->expectation());
        }
    }

    protected function shouldFailWith($parser, string $input, ?string $expectedFailure = null)
    {
        /** @var Result $actual */
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
