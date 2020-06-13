<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Predicates;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{satisfy};
use function Mathias\ParserCombinator\Predicates\{isEqual, notPred};

final class predicatesTest extends ParserTestCase
{
    /** @test */
    public function equals()
    {
        $this->assertTrue(isEqual('x')('x'));
        $this->assertFalse(isEqual('x')('y'));
    }

    /** @test */
    public function not()
    {
        $this->assertFalse(notPred(isEqual('x'))('x'));
        $this->assertTrue(notPred(isEqual('x'))('y'));

        $parser = satisfy(notPred(isEqual('x')));
        $this->assertNotParse($parser, "xyz");
        $this->assertParse("y", $parser, "yz");
        $this->assertNotParse($parser, "", "satisfy(predicate)");
    }
}

