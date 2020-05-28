<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\Internal;

use InvalidArgumentException;

final class Assert
{
    /**
     * @throws InvalidArgumentException
     */
    public static function length(string $value, int $length, string $message)
    {
        if($length !== mb_strlen($value)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function minLength(string $value, int $length, string $message)
    {
        if(mb_strlen($value) < $length) {
            throw new InvalidArgumentException($message);
        }
    }
}