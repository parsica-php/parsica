<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\nothing;
use function Mathias\ParserCombinator\ParseResult\discard;
use function Mathias\ParserCombinator\ParseResult\fail;
use function Mathias\ParserCombinator\ParseResult\succeed;

final class MAppendTest extends ParserTestCase
{
    /** @test */
    public function mappend_strings()
    {
        $succeed1 = succeed("Parsed1", "Remain1");
        $succeed2 = succeed("Parsed2", "Remain2");
        $fail1 = fail("Expected1", "Got1");
        $fail2 = fail("Expected2", "Got2");
        $discard1 = discard("Remain3");
        $discard2 = discard("Remain4");

        $this->assertStrictlyEquals(succeed("Parsed1Parsed2", "Remain2"), $succeed1->mappend($succeed2));
        $this->assertStrictlyEquals(succeed("Parsed1", "Remain3"), $succeed1->mappend($discard1));
        $this->assertStrictlyEquals(fail("Expected1", "Got1"), $succeed1->mappend($fail1));

        $this->assertStrictlyEquals(fail("Expected1", "Got1"), $fail1->mappend($succeed2));
        $this->assertStrictlyEquals(fail("Expected1", "Got1"), $fail1->mappend($discard2));
        $this->assertStrictlyEquals(fail("Expected1", "Got1"), $fail1->mappend($fail2));

        $this->assertStrictlyEquals(succeed("Parsed1", "Remain1"), $discard1->mappend($succeed1));
        $this->assertStrictlyEquals(discard("Remain4"), $discard1->mappend($discard2));
        $this->assertStrictlyEquals(fail("Expected1", "Got1"), $discard1->mappend($fail1));
    }
}
