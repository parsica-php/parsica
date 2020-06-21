<?php /** @noinspection ALL */
declare(strict_types=1);
// This code is executed by UpToDocs before each code block
require_once __DIR__ . '/../vendor/autoload.php';

use Verraes\Parsica\ParserFailure;
use function Verraes\Parsica\{
    between,
    char,
    digitChar ,
    collect,
    sequence,
    float,
    orPred ,
    stringI,
    string,
    recursive,
    atLeastOne,
    alphaChar,
    punctuationChar,
    isEqual,
    optional,
    satisfy,
    isDigit,
    isWhitespace,
    charI
};

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_QUIET_EVAL, 1);

