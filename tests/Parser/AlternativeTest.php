<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;

final class AlternativeTest extends ParserTestCase
{
    /** @test */
    public function alternative()
    {
        $parser = char('a')->alternative(char('b'));
        $this->assertParse("a", $parser, "a123");
        $this->assertParse("b", $parser, "b123");
        $this->assertNotParse($parser, "123");
    }
}
