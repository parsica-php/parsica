<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Exception;
use Mathias\ParserCombinator\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Mathias\ParserCombinator\char;
use function Mathias\ParserCombinator\nothing;

final class AppendTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function append_strings()
    {
        $parser = char('a')->append(char('b'));
        $this->assertParse("ab", $parser, "abc");
    }

    /** @test */
    public function append_array()
    {
        $a = char('a')->map(fn($x) => [$x]);
        $b = char('b')->map(fn($x) => [$x]);
        $this->assertParse(['a', 'b'], $a->append($b), "abc");
    }


    /** @test */
    public function append_non_semigroup()
    {
        $a = char('a')->construct(NotASemigroup::class);
        $b = char('b')->construct(NotASemigroup::class);
        $this->expectException(Exception::class);
        $a->append($b)->run('abc');
    }
}

final class NotASemigroup
{

    public function __construct($_)
    {
    }
}