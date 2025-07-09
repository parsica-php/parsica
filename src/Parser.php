<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica;

use Exception;
use Parsica\Parsica\Internal\Fail;

/**
 * A parser is any function that takes a string input and returns a {@see ParseResult}. The Parser class is a wrapper
 * around such functions. The {@see Parser::make()} static constructor takes a callable that does the actual parsing.
 * Usually you don't need to instantiate this class directly. Instead, build your parser from existing parsers and
 * combinators.
 *
 * At the moment, there is no Parser interface, and no Parser abstract class to extend from. This is intentional, but
 * will be changed if we find use cases where those would be the best solutions.
 *
 * The type is Parser<T>, where T is the type of the output that the parser will produce after completing successfully.
 *
 * @template T
 * @api
 */
final class Parser
{
    /**
     * @psalm-var pure-callable(Stream) : ParseResult<T> $parserF
     */
    private $parserFunction;

    /** @psalm-var 'non-recursive'|'awaiting-recurse'|'recursion-was-setup' */
    private string $recursionStatus;

    private string $label;

    /**
     * @psalm-param pure-callable(Stream) : ParseResult<T> $parserFunction
     * @psalm-param 'non-recursive'|'awaiting-recurse'|'recursion-was-setup' $recursionStatus
     * @psalm-pure
     * @psalm-suppress ImpureVariable
     */
    private function __construct(callable $parserFunction, string $recursionStatus, string $label)
    {
        $this->parserFunction = $parserFunction;
        $this->recursionStatus = $recursionStatus;
        $this->label = $label;
    }

    /**
     *  Create a recursive parser. Used in combination with recurse(Parser).
     *
     * @see recursive()
     *
     * @psalm-return Parser<mixed>
     * @api
     * @psalm-pure
     */
    public static function recursive(): Parser
    {
        return new Parser(
        // Make a placeholder parser that will throw when you try to run it.
            static function (Stream $_): ParseResult {
                throw new Exception(
                    "Can't run a recursive parser that hasn't been setup properly yet. "
                    . "A parser created by recursive(), must then be called with ->recurse(Parser) "
                    . "before it can be used."
                );
            }, 'awaiting-recurse', "<recursive>");
    }

    /**
     * Make a new parser.
     *
     * @internal
     *
     * @template T2
     *
     * @psalm-param pure-callable(Stream):ParseResult<T2> $parserFunction
     *
     * @psalm-return Parser<T2>
     * @psalm-pure
     */
    public static function make(string $label, callable $parserFunction): Parser
    {
        return new Parser($parserFunction, 'non-recursive', $label);
    }

    /**
     * Recurse on a parser. Used in combination with {@see recursive()}. After calling this method, this parser behaves
     * like a regular parser.
     *
     * @psalm-param Parser<mixed> $parser
     *
     * @api
     */
    public function recurse(Parser $parser): void
    {
        switch ($this->recursionStatus) {
            case 'non-recursive':
                throw new Exception(
                    "You can't recurse on a non-recursive parser. Create a recursive parser first using recursive(), "
                    . "then call ->recurse() on it."
                );
            case 'recursion-was-setup':
                throw new Exception("You can only call recurse() once on a recursive parser.");
            case 'awaiting-recurse':
                // Replace the placeholder parser from recursive() with a call to the inner parser. This must be dynamic,
                // because it's possible that the inner parser is also a recursive parser that has not been set up yet.
                $this->parserFunction = fn(Stream $input): ParseResult => $parser->run($input);
                $this->recursionStatus = 'recursion-was-setup';
                $this->label = $parser->getLabel();
                break;
            default:
                throw new Exception("Unexpected recursionStatus value");
        }
    }

    /**
     * Run the parser on an input
     *
     * @psalm-return ParseResult<T>
     * @api
     * @psalm-mutation-free
     */
    public function run(Stream $input): ParseResult
    {
        return ($this->parserFunction)($input);
    }

    /**
     * Optionally parse something, but still succeed if the thing is not there.
     *
     *
     * @psalm-return Parser<T|null>
     * @see optional()
     * @api
     * @psalm-mutation-free
     */
    public function optional(): Parser
    {
        return optional($this);
    }

    /**
     * Try the first parser, and failing that, try the second parser. Returns the first succeeding result, or the first
     * failing result.
     *
     * Caveat: The order matters!
     * string('http')->or(string('https')
     *
     * @psalm-param Parser<T> $other
     *
     * @psalm-return Parser<T>
     * @api
     * @psalm-mutation-free
     */
    public function or(Parser $other): Parser
    {
        return either($this, $other);
    }

    /**
     * Parse something, then follow by something else. Ignore the result of the first parser and return the result of
     * the second parser.
     *
     * @template T2
     * @psalm-param Parser<T2> $second
     * @psalm-return Parser<T2>
     * @api
     * @see sequence()
     * @psalm-mutation-free
     */
    public function followedBy(Parser $second): Parser
    {
        return sequence($this, $second);
    }

    /**
     * Parse something, then follow by something else. Ignore the result of the first parser and return the result of
     * the second parser.
     *
     * @template T2
     * @psalm-param Parser<T2> $second
     * @psalm-return Parser<T2>
     * @api
     * @see sequence()
     * @psalm-mutation-free
     */
    public function sequence(Parser $second): Parser
    {
        return sequence($this, $second);
    }

    /**
     * Parse something, then follow by something else. Ignore the result of the first parser and return the result of
     * the second parser. Alias for sequence().
     *
     * @template T2
     * @psalm-param Parser<T2> $second
     * @psalm-return Parser<T2>
     * @api
     * @see sequence()
     * @psalm-mutation-free
     */
    public function then(Parser $second): Parser
    {
        return sequence($this, $second);
    }

    /**
     * Create a parser that takes the output from the first parser (if successful) and feeds it to the callable. The
     * callable must return another parser. If the first parser fails, the first parser is returned.
     *
     * @template T2
     *
     * @psalm-param pure-callable(T) : Parser<T2> $f
     *
     * @psalm-return Parser<T2>
     * @see bind()
     * @api
     * @psalm-mutation-free
     */
    public function bind(callable $f): Parser
    {
        return bind($this, $f);
    }

    /**
     * Map a function over the parser (which in turn maps it over the result).
     *
     * @template T2
     *
     * @psalm-param pure-callable(T) : T2 $transform
     *
     * @psalm-return Parser<T2>
     * @api
     * @psalm-mutation-free
     */
    public function map(callable $transform): Parser
    {
        return map($this, $transform);
    }

    /**
     * Take the remaining input from the result and parse it.
     *
     * @api
     * @psalm-mutation-free
     */
    public function continueFrom(ParseResult $result): ParseResult
    {
        return $this->run($result->remainder());
    }

    /**
     * Combine the parser with another parser of the same type, which will cause the results to be appended.
     *
     * @psalm-param Parser<T|null> $other
     * @psalm-return Parser<T|null>
     * @api
     * @psalm-mutation-free
     */
    public function append(Parser $other): Parser
    {
        return append($this, $other);
    }

    /**
     * Combine the parser with another parser of the same type, which will cause the results to be appended.
     *
     * @psalm-param Parser<T|null> $other
     * @psalm-return Parser<T|null>
     * @api
     * @psalm-mutation-free
     */
    public function and(Parser $other): Parser
    {
        return append($this, $other);
    }

    /**
     * Try to parse a string. Alias of `try(new StringStream($string))`.
     *
     * @TODO Try should fail when it doesn't consume the whole input.
     *
     * @psalm-param string $input
     *
     * @psalm-return ParseResult<T>
     *
     * @throws ParserHasFailed
     * @api
     */
    public function tryString(string $input): ParseResult
    {
        return $this->try(new StringStream($input));
    }

    /**
     * Try to parse the input, or throw an exception.
     *
     * @TODO Try should fail when it doesn't consume the whole input.
     *
     * @psalm-return ParseResult<T>
     *
     * @throws ParserHasFailed
     * @api
     */
    public function try(Stream $input): ParseResult
    {
        $result = $this->run($input);
        if ($result->isFail()) {
            $result->throw();
        }
        return $result;
    }

    /**
     * Sequential application. Given a parser which outputs a callable, return a new parser that applies the callable on the
     * output of the second parser.
     *
     * The first parser must be of type Parser<callable(T1):T2>. {@see pure()} can be used to wrap a callable in a Parser.
     *
     * Callables with more than 1 argument need to be curried: pure(curry(fn($x, $y)))->apply($parser2)->apply($parser3)
     *
     * @template T2
     * @template T3
     * @psalm-param Parser<T2> $parser
     * @psalm-return Parser<T3>
     * @psalm-suppress MixedArgumentTypeCoercion
     *
     * @api
     * @psalm-mutation-free
     */
    public function apply(Parser $parser): Parser
    {
        return apply($this, $parser);
    }

    /**
     * Sequence two parsers, and return the output of the first one, ignore the second.
     *
     * @api
     * @psalm-mutation-free
     */
    public function thenIgnore(Parser $other): Parser
    {
        return keepFirst($this, $other);
    }

    /**
     * notFollowedBy only succeeds when $second fails. It never consumes any input.
     *
     * Example:
     *
     * `string("print")` will also match "printXYZ"
     *
     * `string("print")->notFollowedBy(alphaNumChar()))` will match "print something" but not "printXYZ something"
     *
     * @psalm-param Parser<T2> $parser
     *
     * @psalm-return Parser<T>
     * @see notFollowedBy()
     *
     * @template T2
     * @api
     * @psalm-mutation-free
     */
    public function notFollowedBy(Parser $second): Parser
    {
        return keepFirst($this, notFollowedBy($second));
    }

    /**
     * The parser's label.
     *
     * @internal
     * @psalm-mutation-free
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Label a parser. When a parser fails, you'll see your label as the "expected" value. As a best practice, the
     * labels should make sense to the person who provides the input for your parser. That's often an end user or a
     * third party, so keep them in mind.
     *
     * @psalm-return Parser<T>
     * @api
     * @psalm-mutation-free
     */
    public function label(string $label): Parser
    {
        $parserFn = $this->parserFunction;

        $newParserFunction = static function (Stream $input) use ($parserFn, $label) : ParseResult {
            /** @psalm-var ParseResult $result */
            $result = ($parserFn)($input);
            return ($result->isSuccess())
                ? $result
                : new Fail($label, $result->got());
        };

        return new Parser($newParserFunction, $this->recursionStatus, $label);
    }

    /**
     * If the parser is successful, call the $receiver function with the output of the parser. The resulting parser
     * behaves identical to the original one. This combinator is useful for expressing side effects during the parsing
     * process. It can be hooked into existing event publishing libraries by using $receiver as an adapter for those.
     * Other use cases are logging, caching, performing an action whenever a value is matched in a long running input
     * stream, ...
     *
     * @psalm-param callable(T): void $receiver
     *
     * @psalm-return Parser<T>
     * @api
     */
    public function emit(callable $receiver): Parser
    {
        return emit($this, $receiver);
    }

    /**
     * Ignore the output of the parser and return the new output instead.
     *
     * @template T2
     * @psalm-param T2 $output
     * @psalm-return Parser<T2>
     *
     * @deprecated @TODO needs test
     * @psalm-mutation-free
     */
    public function voidLeft($output): Parser
    {
        return $this->map(
            /**
             * @psalm-param T $_
             * @psalm-return T2
             */
            fn($_) => $output
        );
    }

    /**
     * Make sure that the input ends after the parser has successfully completed. The output is the output of the
     * original parser.
     *
     * Also useful in unit tests to make sure a parser doesn't consume more than you intended.
     *
     * Alias for $parser->thenIgnore(eof()).
     *
     * @api
     * @psalm-return Parser<T>
     * @psalm-mutation-free
     */
    public function thenEof(): Parser
    {
        return keepFirst($this, eof());
        // aka $this->thenIgnore(eof());
    }
}
