<?php declare(strict_types=1);

namespace Verraes\Parsica;

use Verraes\Parsica\Internal\Assert;
use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Succeed;
use function Verraes\Parsica\ParseResult\{fail, succeed};

/**
 * Parse a non-empty string.
 *
 * @return Parser<string>
 * @see stringI()
 *
 */
function string(string $str): Parser
{
    Assert::nonEmpty($str);
    $len = mb_strlen($str);
    /** @var Parser<string> $parser */
    $parser = Parser::make(
        fn(string $input): ParseResult => mb_substr($input, 0, $len) === $str
            ? new Succeed($str, mb_substr($input, $len))
            : new Fail("string($str)", $input)
    );
    return $parser;
}

/**
 * Parse a non-empty string, case-insensitive and case-preserving. On success it returns the string cased as the
 * actually parsed input.
 * eg stringI("foobar")->run("foObAr") will succeed with "foObAr"
 *
 * @return Parser<string>
 * @see string()
 *
 */
function stringI(string $str): Parser
{
    Assert::nonEmpty($str);
    /** @var list<string> $split */
    $split = mb_str_split($str);
    $chars = array_map(fn(string $c) : Parser => charI($c), $split);
    /** @var Parser<string> $parser */
    $parser = array_reduce(
        $chars,
        fn(Parser $l, Parser $r): Parser => $l->append($r),
        success()
    )->label("stringI($str)");
    return $parser;
}