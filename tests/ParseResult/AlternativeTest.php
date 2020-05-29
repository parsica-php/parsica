<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\ParseResult\fail;
use function Mathias\ParserCombinator\ParseResult\succeed;

final class AlternativeTest extends ParserTestCase
{
    /** @test */
    public function alternative()
    {
        $succeed1 = succeed("S1", "");
        $succeed2 = succeed("S2", "");
        $fail1 = fail("F1", "");
        $fail2 = fail("F2", "");

        $this->assertSame($succeed1, $succeed1->alternative($succeed2));
        $this->assertSame($succeed1, $succeed1->alternative($fail1));
        $this->assertSame($succeed1, $fail1->alternative($succeed1));
        $this->assertSame($fail1, $fail1->alternative($fail2));
    }

}
