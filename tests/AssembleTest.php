<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\assemble;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\string;

final class AssembleTest extends ParserTestCase
{
    /** @test */
    public function assemble_string()
    {
        $parser = assemble(
            string('first'),
            string('second'),
        );
        $this->assertParse("firstsecond", $parser, "firstsecond");
        $this->assertRemain("", $parser, "firstsecond");
    }

    /** @test */
    public function assemble_string_ignore()
    {
        $parser = assemble(
            string('first'),
            char('-')->ignore(),
            string('second'),
        );
        $this->assertParse("firstsecond", $parser, "first-second");
        $this->assertRemain("", $parser, "first-second");
    }

    /** @test */
    public function assemble_arrays()
    {
        $toArray = fn($v) => [$v];

        $parser = assemble(
            string('first')->fmap($toArray),
            string('second')->fmap($toArray),
        );

        $input = "firstsecond";
        $expected = ["first", "second"];

        $this->assertParse($expected, $parser, $input);
    }

    /** @test */
    public function assemble_different_types_but_the_others_are_ignored()
    {
        $toArray = fn($v) => [$v];
        $parser = assemble(
            char('[')->ignore(),
            string('first')->fmap($toArray),
            char(',')->ignore(),
            string('second')->fmap($toArray),
            char(']')->ignore(),
        );

        $input = "[first,second]";
        $expected = ["first", "second"];

        $this->assertParse($expected, $parser, $input);
    }
}
