<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Verraes\Parsica\v0_3_0;

use PHPUnit\Framework\TestCase;
use Verraes\Parsica\Parser;
use Verraes\Parsica\PHPUnit\ParserAssertions;
use function Verraes\Parsica\{alphaChar,
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
        $this->assertParse("a", char('a'), "abc");
        $this->assertRemain("bc", char('a'), "abc");
        $this->assertNotParse(char('a'), "bc", "char(a)");
    }

    /** @test */
    public function charI()
    {
        $this->assertParse("a", charI('a'), "abc");
        $this->assertParse("A", charI('a'), "ABC");
    }

    /** @test */
    public function string()
    {
        $this->assertParse("abc", string('abc'), "abcde");
        $this->assertNotParse(string('abc'), "babc", "string(abc)");
    }

    /** @test */
    public function stringI()
    {
        $parser = stringI('hello world');
        $input = "hElLO WoRlD!!1!";
        $expected = "hElLO WoRlD";
        $this->assertParse($expected, $parser, $input, "stringI() should be case-preserving");
        $this->assertRemain("!!1!", $parser, $input);
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
        $this->assertParse($example, $parser, $example);
    }
}

