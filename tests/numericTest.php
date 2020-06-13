<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\digitChar;
use function Mathias\ParserCombinator\float;

final class numericTest extends ParserTestCase
{
    /** @test */
    public function digit()
    {
        $this->assertParse("1", digitChar(), "1ab");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }

    /** @test */
    public function float()
    {
        $this->assertParse("0", float(), "0");
        $this->assertParse("0.1", float(), "0.1");
        $this->assertParse("123.456", float(), "123.456");
        $this->assertParse(123.456, float()->fmap('floatval'), "123.456");
        $this->markTestIncomplete("@TODO Replace with 0.2 version");
    }
}
