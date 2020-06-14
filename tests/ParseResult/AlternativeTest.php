<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\ParseResult\discard;
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
        $discard1 = discard("");
        $discard2 = discard("");

        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($succeed2));
        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($fail1));
        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($discard1));

        $this->assertStrictlyEquals($succeed1, $fail1->alternative($succeed1));
        $this->assertStrictlyEquals($fail1, $fail1->alternative($fail2));
        $this->assertStrictlyEquals($discard1, $fail1->alternative($discard1));

        $this->assertStrictlyEquals($discard1, $discard1->alternative($succeed1));
        $this->assertStrictlyEquals($discard1, $discard1->alternative($fail1));
        $this->assertStrictlyEquals($discard1, $discard1->alternative($discard2));
    }
}
