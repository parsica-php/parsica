<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Tests;
require_once __DIR__ . '/../vendor/autoload.php';

// @TODO fix, this should be autoloaded
require_once __DIR__.'/../src/Functions.php';

use Mathias\ParserCombinators\Result;
use PHPUnit\Framework\TestCase;
use function Mathias\ParserCombinators\{either, char};

final class CombinatorsTest extends ParserTest
{
    /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));
        $this->shouldParse($parser, "abc", "a");
        $this->shouldParse($parser, "bc", "b");
        $this->shouldParse($parser, "cd", "b");

    }
}
