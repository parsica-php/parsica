<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinators;

use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\digit;
use function Mathias\ParserCombinator\float;
use function Mathias\ParserCombinator\string;

final class BasicParsersTest extends ParserTest
{
    /** @test */
    public function char()
    {
        $this->assertParse(char('a'), "abc", "a");
        $this->assertRemain(char('a'), "abc", "bc");
        $this->assertNotParse(char('a'), "bc", "char(a)");
    }

    /** @test */
    public function string()
    {
        $this->assertParse(string('abc'), "abcde", "abc");
        $this->assertNotParse(string('abc'), "babc");
    }

    /** @test */
    public function digit()
    {
        $this->assertParse(digit(), "1ab", "1");
    }

    /** @test */
    public function float()
    {
        $this->assertParse(float(), "0", "0");
        $this->assertParse(float(), "0.1", "0.1");
        $this->assertParse(float(), "123.456", "123.456");
        $this->assertParse(float()->into1('floatval'), "123.456", 123.456);
    }
}
