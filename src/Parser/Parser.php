<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\Parser;

use Exception;
use Mathias\ParserCombinator\ParseResult\ParseFailure;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail, succeed};
use function Mathias\ParserCombinator\pure;

/**
 * A parser is any function that takes a string input and returns a {@see ParseResult}. The Parser class is a wrapper
 * around such functions. The {@see Parser::make()} static constructor takes a callable that does the actual parsing.
 * Usually you don't need to instantiate this class directly. Instead, build your parser from existing parsers and
 * combinators.
 *
 * At the moment, there is no Parser interface, and no Parser abstract class to extend from. This is intentional, but
 * will be changed if we find use cases where those would be the best solutions.
 *
 * @internal
 * @template T
 */
final class Parser
{
    /**
     * @var callable(string) : ParseResult<T> $parserF
     */
    private $parserFunction;

    /** @var 'non-recursive'|'awaiting-recurse'|'recursion-was-setup' */
    private string $recursionStatus;

    /**
     * @param callable(string) : ParseResult<T> $parserFunction
     * @param 'non-recursive'|'awaiting-recurse'|'recursion-was-setup' $recursionStatus
     */
    private function __construct(callable $parserFunction, string $recursionStatus)
    {
        $this->parserFunction = $parserFunction;
        $this->recursionStatus = $recursionStatus;
    }

    /**
     * Make a recursive parser. Use {@see recursive()}.
     *
     * @return Parser<T>
     */
    public static function recursive(): Parser
    {
        return new Parser(
        // Make a placeholder parser that will throw when you try to run it.
            function (string $input): ParseResult {
                throw new Exception(
                    "Can't run a recursive parser that hasn't been setup properly yet. "
                    . "A parser created by recursive(), must then be called with ->recurse(Parser) "
                    . "before it can be used."
                );
            },
            'awaiting-recurse');
    }

    /**
     * Recurse on a parser. Used in combination with {@see recursive()}. After calling this method, this parser behaves
     * like a regular parser.
     */
    public function recurse(Parser $parser): Parser
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
                // because it's possible that the inner parser is also a recursive parser that has not been setup yet.
                $this->parserFunction = fn(string $input): ParseResult => $parser->run($input);
                $this->recursionStatus = 'recursion-was-setup';
                break;
            default:
                throw new Exception("Unexpected recursionStatus value");
        }

        return $this;
    }

    /**
     * Run the parser on an input
     *
     * @return ParseResult<T>
     */
    public function run(string $input): ParseResult
    {
        $f = $this->parserFunction;
        return $f($input);
    }

    /**
     * Parse something, strip it from the remaining input, but discard the parsed output.
     *
     * @return Parser<T>
     */
    public function ignore(): Parser
    {
        return Parser::make(function (string $input): ParseResult {
            return $this->run($input)->discard();
        });
    }

    /**
     * Make a new parser. This is the constructor for all regular use.
     *
     * @template T2
     *
     * @param callable(string) : ParseResult<T2> $parserFunction
     *
     * @return Parser<T2>
     */
    public static function make(callable $parserFunction): Parser
    {
        return new Parser($parserFunction, 'non-recursive');
    }

    /**
     * @return self
     *
     * @see optional()
     *
     * @return self<string>
     */
    public function optional(): self
    {
        return $this->or(pure(""));
    }

    /**
     * Alias for `sequence()`. Parse something, then follow by something else. Ignore the result of the first parser and return the result of the
     * second parser.
     *
     * @template T2
     *
     * @param Parser<T2> $second
     *
     * @return Parser<T2>
     */
    public function followedBy(Parser $second): Parser
    {
        return $this->sequence($second);
    }

    /**
     * Parse something, then follow by something else. Ignore the result of the first parser and return the result of the
     * second parser.
     *
     * @template T2
     *
     * @param Parser<T2> $second
     *
     * @return Parser<T2>
     * @see sequence()
     */
    public function sequence(Parser $second): Parser
    {
        return $this->bind(
        /** @param mixed $_ */
            function ($_) use ($second) {
                return $second;
            }
        )->label('sequence');
    }

    /**
     * Label a parser. When a parser fails, instead of a generated error message, you'll see your label.
     * eg (char(':')->followedBy(char(')')).followedBy(char(')')).
     *
     * @return Parser<T>
     */
    public function label(string $label): Parser
    {
        // @todo perhaps something like $parser->onSuccess($f)->onFailure($g) ?
        return Parser::make(function (string $input) use ($label) : ParseResult {
            $result = $this->run($input);
            return ($result->isSuccess())
                ? $result
                : fail($label, $input);
        });
    }

    /**
     * Create a parser that takes the output from the first parser (if successful) and feeds it to the callable. The
     * callable must return another parser. If the first parser fails, the first parser is returned.
     *
     * @template T2
     *
     * @param callable(T) : Parser<T2> $f
     *
     * @return Parser<T2>
     * @see bind()
     */
    public function bind(callable $f): Parser
    {
        /** @var Parser<T2> $parser */
        $parser = Parser::make(function (string $input) use ($f) : ParseResult {
            $result = $this->fmap($f)->run($input);
            if ($result->isSuccess()) {
                $p2 = $result->output();
                return $result->continueWith($p2);
            } else {
                return $result;
            }
        });
        return $parser;
    }

    /**
     * Map a function over the parser (which in turn maps it over the result).
     *
     * @template T2
     *
     * @param callable(T) : T2 $transform
     *
     * @return Parser<T2>
     */
    public function fmap(callable $transform): Parser
    {
        return Parser::make(fn(string $input): ParseResult => $this->run($input)->fmap($transform));
    }

    /**
     * Take the remaining input from the result and parse it
     */
    public function continueFrom(ParseResult $result): ParseResult
    {
        return $this->run($result->remainder());
    }

    /**
     * Map the parser into a new object instance
     *
     * @template T2
     *
     * @param class-string<T2> $className
     *
     * @return Parser<T2>
     */
    public function fmapClass(string $className): Parser
    {
        return $this->fmap(
        /** @param mixed $val */
            fn($val) => new $className($val)
        );
    }

    /**
     * Combine the parser with another parser of the same type, which will cause the results to be appended.
     *
     * @param Parser<T> $other
     *
     * @return Parser<T>
     * @see ParseResult::append
     */
    public function append(Parser $other): Parser
    {
        return Parser::make(function (string $input) use ($other): ParseResult {
            $r1 = $this->run($input);
            $r2 = $r1->continueWith($other);
            return $r1->append($r2);
        });
    }

    /**
     * Try the first parser, and failing that, try the second parser. Returns the first succeeding result, or the first
     * failing result.
     *
     * Caveat: The order matters!
     * string('http')->or(string('https')
     *
     * @param Parser<T> $other
     *
     * @return Parser<T>
     */
    public function or(Parser $other): Parser
    {
        // This is the canonical implementation: run both parsers, and pick the first succeeding one, by delegating
        // this work to ParseResult::alternative.

        return Parser::make(function (string $input) use ($other): ParseResult {
            // @TODO When the first parser succeeds, this implementation unnecessarily evaluates $other anyway.
            return $this->run($input)
                ->alternative(
                    $other->run($input)
                );
        });

        // @TODO For a more performant version, we'll probably need to replace the above implementation with this one.
        // The reason is that the above implementation runs both parsers, even if the first one succeeds.
        // The implementation below only runs the second parser if the first one fails.
        /*
        return Parser::make(function (string $input) use ($other): ParseResult {
            $r1 = $this->run($input);
            if($r1->isSuccess()) {
                return $r1;
            }
            $r2 = $other->run($input);
            return $r2->isSuccess() ? $r2 : $r1;
        });
        */
    }

    /**
     * Try to parse the input, or throw an exception;
     *
     * @return ParseResult<T>
     *
     * @throws ParseFailure
     */
    public function try(string $input): ParseResult
    {
        $result = $this->run($input);
        if ($result->isFail()) {
            /** @psalm-suppress InvalidThrow */
            throw $result;
        }
        return $result;
    }

    /**
     * Sequential application.
     *
     * The first parser must be of type Parser<callable(T2):T3>.
     *
     * apply :: f (a -> b) -> f a -> f b
     *
     * @template T2
     * @template T3
     *
     * @param Parser<T2> $parser
     *
     * @return Parser<T3>
     *
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function apply(Parser $parser): Parser
    {
        return $this->bind(fn(callable $f) => $parser->fmap($f));
    }
}