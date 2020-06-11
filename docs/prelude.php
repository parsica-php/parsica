<?php /** @noinspection ALL */
declare(strict_types=1);
// This code is executed by UpToDocs before each code block
require_once __DIR__.'/../vendor/autoload.php';

use function Mathias\ParserCombinator\{char, collect, digit, ignore, skipSpace, string, seq, float, recursive};
use Mathias\ParserCombinator\ParseResult\ParseFailure;