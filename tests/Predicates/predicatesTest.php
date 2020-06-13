<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{satisfy};
use function Mathias\ParserCombinator\Predicates\{equals, not};

final class predicatesTest extends ParserTestCase
{
    /** @test */
    public function equals()
    {
        $this->assertTrue(equals('x')('x'));
        $this->assertFalse(equals('x')('y'));
    }

    /** @test */
    public function not()
    {
        $this->assertFalse(not(equals('x'))('x'));
        $this->assertTrue(not(equals('x'))('y'));

        $parser = satisfy(not(equals('x')));
        $this->assertNotParse($parser, "xyz");
        $this->assertParse("y", $parser, "yz");
        $this->assertNotParse($parser, "", "satisfy(predicate)");
    }
}

