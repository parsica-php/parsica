<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

final class Parser
{
    private $parser;

    function __construct(callable $parser)
    {
        $this->parser = $parser;
    }

    public function __invoke(string $input): ParseResult
    {
        $f = $this->parser;
        return $f($input);
    }
}