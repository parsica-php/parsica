<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\ParseResult\discard;
use function Mathias\ParserCombinator\ParseResult\fail;
use function Mathias\ParserCombinator\ParseResult\succeed;

final class MAppendTest extends ParserTestCase
{
    /** @test */
    public function mappend_strings()
    {
        $succeed1 = succeed("S1", "");
        $succeed2 = succeed("S2", "");
        $fail1 = fail("F1", "");
        $fail2 = fail("F2", "");
        $discard1 = discard("");
        $discard2 = discard("");

        $this->assertStrictlyEquals(succeed("S1S2", ""), $succeed1->mappend($succeed2));
        $this->assertStrictlyEquals($succeed1, $succeed1->mappend($discard2));
        $this->assertStrictlyEquals($fail2, $succeed1->mappend($fail2));

        $this->assertStrictlyEquals($fail1, $fail1->mappend($succeed2));
        $this->assertStrictlyEquals($fail1, $fail1->mappend($discard2));
        $this->assertStrictlyEquals($fail1, $fail1->mappend($fail2));

        $this->assertStrictlyEquals($succeed2, $discard1->mappend($succeed2));
        $this->assertStrictlyEquals($discard1, $discard1->mappend($discard2));
        $this->assertStrictlyEquals($fail2, $discard1->mappend($fail2));

    }

}
