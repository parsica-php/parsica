<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica;

use Parsica\Parsica\Internal\Assert;
use Parsica\Parsica\Internal\EndOfStream;
use Parsica\Parsica\Internal\Fail;
use Parsica\Parsica\Internal\Succeed;
use function Parsica\Parsica\Internal\FP\foldl;

/**
 * Parse a non-empty string.
 *
 * @psalm-return Parser<string>
 * @api
 * @see stringI()
 * @psalm-pure
 */
function string(string $str): Parser
{
    /** @psalm-suppress ImpureMethodCall */
    Assert::nonEmpty($str);
    $len = mb_strlen($str);
    $label = "'$str'";
    /** @psalm-var Parser<string> $parser */
    $parser = Parser::make($label, static function (Stream $input) use ($label, $len, $str): ParseResult {
        try {
            $t = $input->takeN($len);
        } catch (EndOfStream $e) {
            return new Fail($label, $input);
        }
        return $t->chunk() === $str
            ? new Succeed($str, $t->stream())
            : new Fail($label, $input);
    }
    );
    return $parser;
}

/**
 * Parse a non-empty string, case-insensitive, and case-preserving. On success, it returns the string cased as the
 * actually parsed input.
 * eg stringI("foobar")->tryString("foObAr") will succeed with "foObAr"
 *
 * @TODO The implementation could be replaced using Stream::takeWhile
 *
 * @psalm-return Parser<string>
 * @api
 * @see string()
 * @psalm-pure
 */
function stringI(string $str): Parser
{
    /** @psalm-suppress ImpureMethodCall */
    Assert::nonEmpty($str);
    /** @psalm-var list<string> $split */
    $split = mb_str_split($str);
    $chars = array_map(
        fn(string $c): Parser => charI($c),
        $split
    );

    /** @psalm-var Parser<string> $parser */
    $parser = foldl(
        $chars,
        /** @psalm-pure */
        fn(Parser $l, Parser $r): Parser => append($l, $r),
        succeed()
    )->label("'$str'");
    return $parser;
}
