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
    charI,
    sepBy,
    some,
    repeat,
    upperChar,
    skipHSpace,
    whitespace,
    keepFirst,
    alphaNumChar,
    notFollowedBy,
    choice
};

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_QUIET_EVAL, 1);

