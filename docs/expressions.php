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


use Parsica\Parsica\Parser;
use function Parsica\Parsica\Expression\{binaryOperator,
    expression,
    leftAssoc,
    postfix,
    prefix,
    rightAssoc,
    unaryOperator};
use function Parsica\Parsica\{atLeastOne, between, char, choice, digitChar, keepFirst, recursive, skipHSpace, string, alphaChar};
use function PHPUnit\Framework\{assertSame, assertEquals};

assert_options(ASSERT_ACTIVE, 1);


$token = fn(Parser $parser) => keepFirst($parser, skipHSpace());
$term = fn(): Parser => $token(atLeastOne(digitChar()))->map('intval');
$parens = fn (Parser $parser): Parser =>  $token(between($token(char('(')), $token(char(')')), $parser));

