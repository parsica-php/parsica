<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Exception;
use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\nothing;

final class AppendTest extends ParserTestCase
{
    /** @test */
    public function append_strings()
    {
       $parser = char('a')->append(char('b'));
       $this->assertParse("ab", $parser, "abc");
    }

    /** @test */
    public function append_array()
    {
        $a = char('a')->fmap(fn($x) => [$x]);
        $b = char('b')->fmap(fn($x) => [$x]);
        $this->assertParse(['a', 'b'], $a->append($b), "abc");
    }


    /** @test */
    public function append_non_semigroup()
    {
        $a = char('a')->fmapClass(NotASemigroup::class);
        $b = char('b')->fmapClass(NotASemigroup::class);
        $this->expectException(Exception::class);
        $a->append($b)->run('abc');
    }

    /** @test */
    public function append_nothing()
    {
        $parser = nothing()->append(char('a'));
        $this->assertParse("a", $parser, "ab");
        $this->assertRemain("b", $parser, "ab");

        $parser = char('a')->append(nothing());
        $this->assertParse("a", $parser, "ab");
        $this->assertRemain("b", $parser, "ab");

        $parser = nothing()->append(nothing());
        $this->assertRemain("ab", $parser, "ab");
    }
}

final class NotASemigroup {

    public function __construct($_)
    {
    }
}