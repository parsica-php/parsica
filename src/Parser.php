<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{parser, succeed, fail};

/**
 * @template T
 */
final class Parser
{
    /**
     * @psalm-param callable(string):ParseResult<T> $parser
     */
    private $parser;

    /**
     * @param callable(string):ParseResult<T> $parser
     */
    function __construct(callable $parser)
    {
        $this->parser = $parser;
    }

    /** @todo replace by a simple parse method? */
    public function __invoke(string $input): ParseResult
    {
        $f = $this->parser;
        return $f($input);
    }

    /**
     * Label a parser. When a parser fails, instead of a generrated error message, you'll see your label.
     * eg (char(':')->followedBy(char(')')).followedBy(char(')')).
     */
    public function label(string $label): Parser
    {
        return parser(function (string $input) use ($label) : ParseResult {
            $result = $this($input);
            return ($result->isSuccess())
                ? $result
                : fail($label, $input);
        });
    }

    /**
     * @see ignore()
     */
    public function ignore(): Parser
    {
        return $this->into1(fn(string $_) => "");
    }

    /**
     * @deprecated 0.2
     * @see optional()
     */
    public function optional(): Parser
    {
        return parser(function (string $input): ParseResult {
            $r1 = $this($input);
            if ($r1->isSuccess()) {
                return $r1;
            } else {
                return succeed("", $input);
            }
        });
    }

    /**
     * @deprecated 0.2
     * @see seq()
     */
    public function followedBy(Parser $second): Parser
    {
        return parser(function (string $input) use ($second) : ParseResult {
            $r1 = $this($input);
            if ($r1->isSuccess()) {
                $r2 = $second($r1->remaining());
                if ($r2->isSuccess()) {
                    return succeed($r1->parsed() . $r2->parsed(), $r2->remaining());
                }
                return fail("seq ({$r1->parsed()} {$r2->expected()})", "@TODO");
            }
            return fail("seq ({$r1->expected()} ...)", "@TODO");
        });

    }

    /**
     * @see either()
     */
    public function or(Parser $second): Parser
    {
        return parser(function (string $input) use ($second) : ParseResult {
            $r1 = $this($input);
            if ($r1->isSuccess()) {
                return $r1;
            }

            $r2 = $second($input);
            if ($r2->isSuccess()) {
                return $r2;
            }

            $expectation = "either ({$r1->expected()} or {$r2->expected()})";
            return fail($expectation, "@TODO");
        });
    }

    /**
     * @deprecated 0.2
     * @see into1()
     *
     * @template T2
     * @param callable(T):T2 $transform
     * @return Parser<T2>
     */
    public function into1(callable $transform): Parser
    {
        return into1($this, $transform);
    }

    /**
     * @deprecated 0.2
     * @param class-string<T2> $className
     *
     * @return Parser<T2>
     * @see intoNew1()
     *
     * @template T2
     */
    public function intoNew1(string $className): Parser
    {
        return $this->into1(
            fn(string $val) => new $className($val)
        );
    }
}