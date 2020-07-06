<?php /**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ /** @noinspection ALL */
declare(strict_types=1);
// This code is executed by UpToDocs before each code block
require_once __DIR__ . '/../vendor/autoload.php';

use Verraes\Parsica\ParserFailure;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\{
    alphaChar,
    alphaNumChar,
    atLeastOne,
    between,
    char,
    charI,
    choice,
    collect,
    digitChar ,
    either,
    float,
    isDigit,
    isEqual,
    isWhitespace,
    keepFirst,
    many,
    notFollowedBy,
    optional,
    orPred ,
    punctuationChar,
    recursive,
    repeat,
    satisfy,
    sepBy,
    sequence,
    skipHSpace,
    some,
    string,
    stringI,
    upperChar,
    whitespace,
};

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_QUIET_EVAL, 1);

