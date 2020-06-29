<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica;

/**
 * If the parser is successful, call the $receiver function with the output of the function. The resulting parser
 * behaves identical to the original one. This combinator is useful for expressing side effects during the parsing
 * process. It can be hooked into existing event publishing libraries by using $receiver as an adapter for those. Other
 * usecases are logging, caching, performing an action whenever a value is matched in a long running input stream, ...
 *
 * @template T
 *
 * @param Parser<T> $parser
 * @param callable(T): void $receiver
 *
 * @return Parser<T>
 */
function emit(Parser $parser, callable $receiver): Parser
{
    return Parser::make(function (string $input) use ($receiver, $parser): ParseResult {
        $result = $parser->run($input);
        if ($result->isSuccess()) {
            $receiver($result->output());
        }
        return $result;
    });
}
