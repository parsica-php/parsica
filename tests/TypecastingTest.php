<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{char, either, ignore, into1, intoNew1, optional, seq, string};

final class TypecastingTest extends ParserTestCase
{
    /** @test */
    public function into1()
    {
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
        $parser =
            char('a')->followedBy(char('b'))
                ->into1('strtoupper');

        $expected = "AB";

        $this->assertParse($expected, $parser, "abc");
    }
    
    
    /** @test */
    public function intoNew1()
    {
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
        $parser = intoNew1(
            seq(char('a'), char('b')),
            __NAMESPACE__.'\\MyType1'
        );

        $expected = new MyType1("ab");

        $this->assertParse($expected, $parser, "abc");
    }

}

class MyType1
{
    private $val;

    function __construct($val)
    {
        $this->val = $val;
    }
}