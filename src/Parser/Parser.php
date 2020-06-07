<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\Parser;

use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail, succeed};

/**
 * @template T
 */
final class Parser
{
    /**
     * @var callable(string) : ParseResult<T> $parserF
     */
    private $parserF;

    /**
     * @param callable(string) : ParseResult<T> $parserF
     */
    function __construct(callable $parserF)
    {
        $this->parserF = $parserF;
    }

    /**
     * @deprecated Quick attempt at making recursive parsers possible by mutating them.
     */
    public function mutate(Parser $parser)
    {
        $this->parserF = $parser->parserF;
    }

    /**
     * Label a parser. When a parser fails, instead of a generated error message, you'll see your label.
     * eg (char(':')->followedBy(char(')')).followedBy(char(')')).
     *
     * @return Parser<T>
     */
    public function label(string $label): Parser
    {
        return new Parser(function (string $input) use ($label) : ParseResult {
            $result = $this->run($input);
            return ($result->isSuccess())
                ? $result
                : fail($label, $input);
        });
    }

    /**
     * Run the parser on an input
     *
     * @return ParseResult<T>
     */
    public function run(string $input): ParseResult
    {
        $f = $this->parserF;
        return $f($input);
    }

    /**
     * Parse something, strip it from the remaining input, but discard the parsed value.
     *
     * @return Parser<T>
     */
    public function ignore(): Parser
    {
        return new Parser(function (string $input) : ParseResult {
            return $this->run($input)->discard();
        });
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
        return new Parser(fn(string $input): ParseResult => $this->run($input)->fmap($transform));
    }

    /**
     * @return Parser<T>
     * @see optional()
     */
    public function optional(): Parser
    {
        return new Parser(function (string $input): ParseResult {
            $r1 = $this->run($input);
            return $r1->isSuccess() ? $r1 : succeed("", $input);
        });
    }

    /**
     * @param Parser<T2> $second
     *
     * @return Parser<T2>
     * @deprecated 0.2
     * @see seq()
     * @template T2
     */
    public function followedBy(Parser $second): Parser
    {
        return new Parser(function (string $input) use ($second) : ParseResult {
            $r1 = $this->run($input);
            if ($r1->isSuccess()) {
                $r2 = $second->continueFrom($r1);
                if ($r2->isSuccess()) {
                    return succeed($r2->parsed(), $r2->remaining());
                }
                return fail("seq (... {$r2->expected()})", $r2->got());
            }
            return fail("seq ({$r1->expected()} ...)", $r1->got());
        });

    }

    /**
     * Take the remaining input from the result and parses it
     * @deprecated Doesn't have a test
     */
    public function continueFrom(ParseResult $result): ParseResult
    {
        return $this->run($result->remaining());
    }

    /**
     * @param Parser<T> $second
     *
     * @return Parser<T>
     * @deprecated 0.2
     * @see either()
     */
    public function or(Parser $second): Parser
    {
        return new Parser(function (string $input) use ($second) : ParseResult {
            $r1 = $this->run($input);
            if ($r1->isSuccess()) {
                return $r1;
            }

            $r2 = $second->run($input);
            if ($r2->isSuccess()) {
                return $r2;
            }

            $expectation = "({$r1->expected()} or {$r2->expected()})";
            return fail($expectation, "@TODO");
        });
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
     *
     * Combine the parser with another parser of the same type, which will cause the results to be mappended.
     *
     * @param Parser<T> $other
     *
     * @return Parser<T>
     * @see ParseResult::mappend
     */
    public function mappend(Parser $other) : Parser
    {
        return new Parser(function (string $input) use ($other): ParseResult {
            $r1 = $this->run($input);
            $r2 = $r1->continueOnRemaining($other);
            return $r1->mappend($r2);
        });
    }
}