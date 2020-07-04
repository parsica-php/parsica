<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica;

use Verraes\Parsica\Internal\Assert;
use Verraes\Parsica\Internal\EndOfStream;
use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Stream;
use Verraes\Parsica\Internal\Succeed;

/**
 * Parse a non-empty string.
 *
 * @psalm-return Parser<string>
 * @api
 * @see stringI()
 *
 */
function string(string $str): Parser
{
    Assert::nonEmpty($str);
    $len = mb_strlen($str);
    /** @psalm-var Parser<string> $parser */
    $parser = Parser::make(
        function (Stream $input) use ($len, $str): ParseResult {
            try {
                $t = $input->takeN($len);
            } catch (EndOfStream $e) {
                return new Fail("string($str)", $input);
            }
            return $t->chunk() === $str
                ? new Succeed($str, $t->stream())
                : new Fail("string($str)", $input);
        }
    );
    return $parser;
}

/**
 * Parse a non-empty string, case-insensitive and case-preserving. On success it returns the string cased as the
 * actually parsed input.
 * eg stringI("foobar")->run("foObAr") will succeed with "foObAr"
 *
 * @psalm-return Parser<string>
 * @api
 * @see string()
 */
function stringI(string $str): Parser
{
    Assert::nonEmpty($str);
    /** @var list<string> $split */
    $split = mb_str_split($str);
    $chars = array_map(fn(string $c): Parser => charI($c), $split);
    /** @var Parser<string> $parser */
    $parser = array_reduce(
        $chars,
        fn(Parser $l, Parser $r): Parser => $l->append($r),
        success()
    )->label("stringI($str)");
    return $parser;
}
