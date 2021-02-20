<?php
declare(strict_types=1);

namespace Tests\Parsica\Parsica;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Parser;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\between;
use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\digitChar;
use function Parsica\Parsica\eof;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\recursive;
use function Parsica\Parsica\skipHSpace;

final class PolishNotationTest extends TestCase
{
    use ParserAssertions;

    /**
     * @var Parser
     */
    private Parser $expr;

    public function setUp(): void
    {
        $token = fn(Parser $parser) => keepFirst($parser, skipHSpace());
        $term = digitChar();
        $parens = fn(Parser $parser)
        : Parser => $token(between($token(char('(')), $token(char(')')), $parser));


        $expr = recursive();


        $plus = collect(
            $token(char('+')),
            $token($expr),
            $token($expr)
        )->map(fn($o) => "(+ {$o[1]} {$o[2]})");

        $times = collect(
            $token(char('*')),
            $token($expr),
            $token($expr)
        )->map(fn($o) => "(* {$o[1]} {$o[2]})");

        $expr->recurse($term->or($plus)->or($times)->or($parens($expr)));

        $this->expr = $expr->thenEof();
    }

    /**
     * @test
     * @dataProvider polishExamples
     */
    public function polishNotation(string $input, $output)
    {
        $this->assertParses($input, $this->expr, $output);
    }

    public function polishExamples()
    {
        $examples = [
            ['1', '1'],
            ['+ 1 2', '(+ 1 2)'],
            ['+ 1 + 2 3', '(+ 1 (+ 2 3))'],
            ['+ + 1 2 + 3 4', '(+ (+ 1 2) (+ 3 4))'],
            ['+ 1 (+ 2 3)', '(+ 1 (+ 2 3))'],
            ['((+ 1 + 2 (+ 3 4)))', '(+ 1 (+ 2 (+ 3 4)))'],
            ['(1)', '1'],
            ['((1))', '1'],
            ['(((1)))', '1'],
            ['* 1 2', '(* 1 2)'],
            ['+ 1 * 2 3', '(+ 1 (* 2 3))'],
            ['* 1 + 2 3', '(* 1 (+ 2 3))'],
            ['* 1 * 2 3', '(* 1 (* 2 3))'],
            ['((+ 1 * 2 (+ 3 4)))', '(+ 1 (* 2 (+ 3 4)))'],
        ];

        return array_combine(array_column($examples, 0), $examples);
    }

    /**
     * @test
     * @dataProvider badExamples
     */
    public function theseAreNotPolishNotation(string $input)
    {
        $this->assertParseFails($input, $this->expr);
    }

    public function badExamples()
    {
        $examples = [
            [''],
            ['()'],
            ['1 2'],
            ['(1 2'],
            ['1 2)'],
            ['(1 2)'],
            ['(+ 2)'],
            ['+ 1 2)'],
            ['(1 + 2)'],
            ['1 + 2)'],
            ['(1 2 +)'],
        ];

        return array_combine(array_column($examples, 0), $examples);
    }
}

