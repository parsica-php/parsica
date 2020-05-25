<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Tests;
require_once __DIR__ . '/../vendor/autoload.php';

// @TODO fix, this should be autoloaded
require_once __DIR__ . '/../src/Functions.php';

use Mathias\ParserCombinators\Result;
use PHPUnit\Framework\TestCase;
use function Mathias\ParserCombinators\{either, char};

final class BasicParsersTest extends ParserTest
{
    /** @test */
    public function char()
    {
        $this->shouldParse(char('a'), "abc", "a");
        $this->shouldFailWith(char('a'), "bc", "char(a)");
    }

}
