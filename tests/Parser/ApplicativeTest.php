<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Cypress\Curry\curry;
use function Mathias\ParserCombinator\{anything, char, digitChar, keepFirst, keepSecond, pure, string};

final class ApplicativeTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function pure()
    {
        $parser = pure("<3");
        $this->assertParse("<3", $parser, "(╯°□°)╯");
    }

    /** @test */
    public function sequential_application()
    {
        $upper = pure(fn(string $v) => strtoupper($v));
        $hello = string('hello');

        // Parser<callable(a):b> -> Parser<a> -> Parser<b>
        $parser = $upper->apply($hello);
        $this->assertParse("HELLO", $parser, "hello");
    }

    /** @test */
    public function sequential_application_2()
    {
        $multiply = curry(fn($x, $y) => $x * $y);
        $number = digitChar()->fmap(fn($s) => intval($s));

        // Parser<callable(a, b):c> -> Parser<a> -> Parser<b> -> Parser<c>
        $parser = pure($multiply)->apply($number)->apply($number);
        $input = "35";
        $this->assertParse(15, $parser, $input);
    }

    /** @test */
    public function sequential_application_3()
    {
        $sort3 = curry(function($x, $y, $z) {
            $arr = [$x, $y, $z];
            sort($arr);
            return implode('', $arr);
        });

        $parser = pure($sort3)->apply(anything())->apply(anything())->apply(anything());
        $this->assertParse("357", $parser, "735");
        $this->assertParse("abc", $parser, "cba");
    }

    /** @test */
    public function keepFirst()
    {
        $parser = keepFirst(char('a'), char('b'));
        $this->assertParse("a", $parser, "abc");
        $this->assertRemain("c", $parser, "abc");
        $this->assertNotParse($parser, "ac");
    }

    /** @test */
    public function keepSecond()
    {
        $parser = keepSecond(char('a'), char('b'));
        $this->assertParse("b", $parser, "abc");
        $this->assertRemain("c", $parser, "abc");
        $this->assertNotParse($parser, "ac");
    }

}

