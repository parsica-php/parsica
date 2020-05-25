<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

/**
 * @method seq(Parser $char) : self
 * @method into1(callable $f) : self
 * @method intoNew1(string $className) : self
 * @method optional() : self
 * @method ignore() : self
 */
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

    public function __call(string $name, array $arguments) : Parser
    {
        array_unshift($arguments, $this);
        return call_user_func_array("Mathias\\ParserCombinators\\".$name, $arguments);
    }
}