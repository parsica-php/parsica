<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\Internal\Fail;
use Mathias\ParserCombinator\Internal\Succeed;
use Mathias\ParserCombinator\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;

final class AlternativeTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function alternative()
    {
        $succeed1 = new Succeed("S1", "");
        $succeed2 = new Succeed("S2", "");
        $fail1 = new Fail("F1", "");
        $fail2 = new Fail("F2", "");

        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($succeed2));
        $this->assertStrictlyEquals($succeed1, $succeed1->alternative($fail1));
        $this->assertStrictlyEquals($succeed1, $fail1->alternative($succeed1));
        $this->assertStrictlyEquals($fail1, $fail1->alternative($fail2));
    }
}
