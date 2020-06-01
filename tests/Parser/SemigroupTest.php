<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\char;

final class SemigroupTest extends ParserTestCase
{
    /** @test */
    public function mappend_strings()
    {
       $parser = char('a')->mappend(char('b'));
       $this->assertParse("ab", $parser, "abc");
    }

    /** @test */
    public function mappend_array()
    {
        $a = char('a')->fmap(fn($x) => [$x]);
        $b = char('b')->fmap(fn($x) => [$x]);
        $this->assertParse(['a', 'b'], $a->mappend($b), "abc");
    }


    /** @test */
    public function mappend_non_semigroup()
    {
        $a = char('a')->fmapClass(NotASemigroup::class);
        $b = char('b')->fmapClass(NotASemigroup::class);
        $this->expectException(\Exception::class);
        $a->mappend($b)->run('abc');
    }


}

final class NotASemigroup {

    function __construct($_)
    {
    }
}