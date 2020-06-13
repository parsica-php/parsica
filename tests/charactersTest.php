<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{char, charI, string, stringI};

final class charactersTest extends ParserTestCase
{
    /** @test */
    public function char()
    {
        $this->assertParse("a", char('a'), "abc");
        $this->assertRemain("bc", char('a'), "abc");
        $this->assertNotParse(char('a'), "bc", "char(a)");
    }

    /** @test */
    public function charI()
    {
        $this->assertParse("a", charI('a'), "abc");
        $this->assertParse("A", charI('a'), "ABC");
    }

    /** @test */
    public function string()
    {
        $this->assertParse("abc", string('abc'), "abcde");
        $this->assertNotParse(string('abc'), "babc", "string(abc)");
    }

    /** @test */
    public function stringI()
    {
        $this->assertParse("hElLO WoRlD", stringI('hello world'), "hElLO WoRlD");
    }
}

