<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTest;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\digit;
use function Mathias\ParserCombinator\float;
use function Mathias\ParserCombinator\string;

final class BasicParsersTest extends ParserTest
{


    /** @test */
    public function string()
    {
        $this->assertParse("abc", string('abc'), "abcde");
        $this->assertNotParse(string('abc'), "babc");
    }

    /** @test */
    public function digit()
    {
        $this->assertParse("1", digit(), "1ab");
    }

    /** @test */
    public function float()
    {
        $this->assertParse("0", float(), "0");
        $this->assertParse("0.1", float(), "0.1");
        $this->assertParse("123.456", float(), "123.456");
        $this->assertParse(123.456, float()->into1('floatval'), "123.456");
    }
}
