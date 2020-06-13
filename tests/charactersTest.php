<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{char, charI, controlChar, string, stringI};

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

    public function characterParsers(): array
    {
        return [
            // dataSet => [Parser, example string]
            'controlChar' => [controlChar(), mb_chr(0x05)],
        ];

    }

    /**
     * @test
     * @dataProvider characterParsers
     */
    public function a_whole_bunch_of_character_parsers(Parser $parser, string $example)
    {
        $this->assertParse($example, $parser, $example);
    }


}

