<?php declare(strict_types=1);

use Mathias\ParserCombinator\Parser;


/**
 * @return mixed
 */
function runParser(Parser $parser, string $input)
{
    $result = $parser($input);
    return $result->parsed();
}
