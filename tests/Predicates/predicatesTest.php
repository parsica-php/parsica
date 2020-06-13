<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Predicates;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{satisfy};
use function Mathias\ParserCombinator\Predicates\{isEqual, notPred, orPred};

final class predicatesTest extends ParserTestCase
{
    /** @test */
    public function isEqual()
    {
        $this->assertTrue(isEqual('x')('x'));
        $this->assertFalse(isEqual('x')('y'));
    }

    /** @test */
    public function notPred()
    {
        $this->assertFalse(notPred(isEqual('x'))('x'));
        $this->assertTrue(notPred(isEqual('x'))('y'));

        $parser = satisfy(notPred(isEqual('x')));
        $this->assertNotParse($parser, "xyz");
        $this->assertParse("y", $parser, "yz");
        $this->assertNotParse($parser, "", "satisfy(predicate)");
    }

    /** @test */
    public function orPred()
    {
        $predicate = orPred(isEqual('x'), isEqual('y'));
        $this->assertTrue($predicate('x'));
        $this->assertTrue($predicate('y'));
        $this->assertFalse($predicate('z'));
    }

    /** @test */
    public function andPred()
    {
        $predicate = orPred(isEqual('x'), isEqual('x'));
        $this->assertTrue($predicate('x'));
        $this->assertFalse($predicate('y'));
    }
}

