<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @noinspection ALL */

namespace Docs;

// This code is executed by UpToDocs before each code block
require_once __DIR__ . '/../vendor/autoload.php';

use Verraes\Parsica\Parser;
use Verraes\Parsica\ParserHasFailed;
use Verraes\Parsica\MBStringStream;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\{alphaChar,
    alphaNumChar,
    atLeastOne,
    between,
    char,
    charI,
    choice,
    collect,
    digitChar,
    either,
    eof,
    Expression\binaryOperator,
    Expression\expression,
    Expression\leftAssoc,
    Expression\nonAssoc,
    Expression\postfix,
    Expression\prefix,
    Expression\rightAssoc,
    Expression\unaryOperator,
    float,
    isDigit,
    isEqual,
    isWhitespace,
    keepFirst,
    many,
    notFollowedBy,
    optional,
    orPred,
    punctuationChar,
    recursive,
    repeat,
    satisfy,
    sepBy,
    sepBy1,
    sequence,
    skipHSpace,
    skipSpace1,
    some,
    string,
    stringI,
    takeWhile,
    upperChar,
    whitespace,
    zeroOrMore};
use function PHPUnit\Framework\{assertEquals, assertFalse, assertInstanceOf, assertIsString, assertTrue, assertSame};
