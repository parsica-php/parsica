<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

use function Mathias\ParserCombinators\{fail, into1, parser, succeed};

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

    public function __invoke(string $input): ParseResult
    {
        $f = $this->parser;
        return $f($input);
    }

    /**
     * @see ignore()
     */
    public function ignore(): Parser
    {
        return $this->into1(fn(string $_) => "");
    }

    /**
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
                return fail("seq ({$r1->parsed()} {$r2->expectation()})");
            }
            return fail("seq ({$r1->expectation()} ...)");
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

            $expectation = "either ({$r1->expectation()} or {$r2->expectation()})";
            return fail($expectation);
        });
    }

    /**
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
     * @see intoNew1()
     *
     * @template T2
     * @param class-string<T2> $className
     * @return Parser<T2>
     */
    public function intoNew1(string $className): Parser
    {
        return $this->into1(
            fn(string $val) => new $className($val)
        );
    }
}