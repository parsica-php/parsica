<?php /** @noinspection ALL */
declare(strict_types=1);
// This code is executed by UpToDocs before each code block
require_once __DIR__ . '/../vendor/autoload.php';

use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\{anything,
    char,
    collect,
    digitChar,
    ignore,
    noneOf,
    skipSpace,
    string,
    stringI,
    sequence,
    float,
    recursive,
    skipSpace1,
    punctuationChar,
    atLeastOne,
    optional
};
use function Mathias\ParserCombinator\Predicates\{isEqual, orPred};
use Mathias\ParserCombinator\ParseResult\ParseFailure;

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_QUIET_EVAL, 1);

