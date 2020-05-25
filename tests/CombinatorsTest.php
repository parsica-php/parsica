<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\char;
use function Mathias\ParserCombinators\either;

final class CombinatorsTest extends ParserTest
{
    /** @test */
    public function either()
    {
        $parser = either(char('a'), char('b'));

        $this->shouldParse($parser, "abc", "a");
        $this->shouldParse($parser, "bc", "b");
        $this->shouldNotParse($parser, "cd");
    }
}
