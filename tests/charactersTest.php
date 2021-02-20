<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\Parser;
use Parsica\Parsica\PHPUnit\ParserAssertions;
use function Parsica\Parsica\{alphaChar,
    alphaNumChar,
    binDigitChar,
    blank,
    char,
    charI,
    controlChar,
    digitChar,
    hexDigitChar,
    lowerChar,
    octDigitChar,
    printChar,
    punctuationChar,
    space,
    string,
    stringI,
    tab,
    upperChar,
    whitespace};

final class charactersTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function char()
    {
        $this->assertParses("abc", char('a'), "a");
        $this->assertRemainder("abc", char('a'), "bc");
        $this->assertParseFails("bc", char('a'), "'a'");
    }

    /** @test */
    public function charI()
    {
        $this->assertParses("abc", charI('a'), "a");
        $this->assertParses("ABC", charI('a'), "A");
    }

    /** @test */
    public function charI_label()
    {
        $this->assertParseFails("foo", charI('a'), "'a' or 'A'");
        $this->assertParseFails("foo", charI('%'), "'%'");
    }

    /** @test */
    public function string()
    {
        $this->assertParses("abcde", string('abc'), "abc");
        $this->assertParseFails("babc", string('abc'), "'abc'");
    }

    /** @test */
    public function stringI()
    {
        $parser = stringI('hello world');
        $input = "hElLO WoRlD!!1!";
        $expected = "hElLO WoRlD";
        $this->assertParses($input, $parser, $expected, "stringI() should be case-preserving");
        $this->assertRemainder($input, $parser, "!!1!");
    }

    public function characterParsers(): array
    {
        $tests = [
            // dataSet => [Parser, example character]
            'controlChar' => [controlChar(), mb_chr(0x05)],
            'printChar_a' => [printChar(), "a"],
            'printChar_%' => [printChar(), "%"],
        ];


        $types = [
            // dataSet => [Parser, [example character]]
            'upperChar' => [upperChar(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ"],
            'lowerChar' => [lowerChar(), "abcdefghijklmnopqrstuvwxyz"],
            'alphaChar' => [alphaChar(), "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"],
            'alphaNumChar' => [alphaNumChar(), "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"],
            'digitChar' => [digitChar(), "0123456789"],
            'binDigitChar' => [binDigitChar(), "01"],
            'octDigitChar' => [octDigitChar(), "01234567"],
            'hexDigitChar' => [hexDigitChar(), "0123456789abcdefABCDEF"],
            'punctuationChar' => [punctuationChar(), "!\"#$%&'()*+,-./:;<=>?@[\]^_`{|}~"],
            'whitespace' => [whitespace(), " \t\n\r\f\v"],
            'space' => [space(), " "],
            'tab' => [tab(), "\t"],
            'blank' => [blank(), "\t "],
        ];

        foreach ($types as $name => [$parser, $chars]) {
            foreach (mb_str_split($chars) as $char) {
                $tests["{$name}: {$char}"] = [$parser, $char];
            }
        }

        return $tests;

    }

    /**
     * @test
     * @dataProvider characterParsers
     */
    public function character_parsers(Parser $parser, string $example)
    {
        $this->assertParses($example, $parser, $example);
    }
}

