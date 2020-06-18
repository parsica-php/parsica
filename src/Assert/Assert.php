<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\Assert;

use InvalidArgumentException;

final class Assert
{
    /**
     * @throws InvalidArgumentException
     */
    public static function nonEmpty(string $str): void
    {
        Assert::minLength($str, 1, "The string must not be empty.");
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function minLength(string $value, int $length, string $message): void
    {
        if (mb_strlen($value) < $length) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param list<string> $chars
     *
     * @throws InvalidArgumentException
     *
     */
    public static function singleChars(array $chars): void
    {
        foreach ($chars as $char) {
            Assert::singleChar($char);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function singleChar(string $char): void
    {
        Assert::length($char, 1, "The argument must be a single character");
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function length(string $value, int $length, string $message): void
    {
        if ($length !== mb_strlen($value)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function atLeastOneArg(array $args, string $source) : void
    {
        if (0 == count($args)) {
            throw new InvalidArgumentException("$source expects at least one Parser");
        }
    }
}
