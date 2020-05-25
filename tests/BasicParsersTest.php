<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinators\char;

final class BasicParsersTest extends ParserTest
{
    /** @test */
    public function char()
    {
        $this->shouldParse(char('a'), "abc", "a");
        $this->shouldNotParse(char('a'), "bc", "char(a)");
    }

}
