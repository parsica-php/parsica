<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

/**
 * @method seq(Parser $char) : Parser
 * @method into(callable $f) : Parser
 * @method intoNew(callable $f) : Parser
 * @method optional() : Parser
 * @method ignore() : Parser
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

    public function __call($name, $arguments) : Parser
    {
        array_unshift($arguments, $this);
        return call_user_func_array("Mathias\\ParserCombinators\\".$name, $arguments);
    }
}