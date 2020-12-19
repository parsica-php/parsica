<?php
declare(strict_types=1);

namespace Tests\Verraes\Parsica;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\digitChar;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\recursive;
use function Verraes\Parsica\skipHSpace;

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


        $expr->recurse($term->or($plus)->or($parens($expr)));

        $this->expr = $expr;
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
            ['()'],
        ];

        return array_combine(array_column($examples, 0), $examples);
    }

}

