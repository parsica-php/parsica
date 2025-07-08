<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\Internal;

use InvalidArgumentException;

/**
 * @internal
 * @psalm-immutable
 */
final class Assert
{
    private function __construct()
    {
    }
    /**
     * @throws InvalidArgumentException
     * @internal
     */
    public static function nonEmpty(string $str): void
    {
        Assert::minLength($str, 1, "The string must not be empty.");
    }

    /**
     * @psalm-assert list $l
     * @psalm-assert !empty $l
     * @throws InvalidArgumentException
     */
    public static function nonEmptyList(array $l, string $message): void
    {
        if (empty($l)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @throws InvalidArgumentException
     * @internal
     */
    public static function minLength(string $value, int $length, string $message): void
    {
        if (mb_strlen($value) < $length) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @psalm-param list<string> $chars
     *
     * @throws InvalidArgumentException
     * @internal
     */
    public static function singleChars(array $chars): void
    {
        foreach ($chars as $char) {
            Assert::singleChar($char);
        }
    }

    /**
     * @throws InvalidArgumentException
     * @internal
     */
    public static function singleChar(string $char): void
    {
        Assert::length($char, 1, "The argument must be a single character");
    }

    /**
     * @throws InvalidArgumentException
     * @internal
     */
    public static function length(string $value, int $length, string $message): void
    {
        if ($length !== mb_strlen($value)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @psalm-param mixed $f
     * @internal
     * @param callable|mixed $f
     */
    public static function isCallable($f, string $message) : void
    {
        if (!is_callable($f)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @throws InvalidArgumentException
     * @internal
     */
    public static function atLeastOneArg(array $args, string $source): void
    {
        if (0 == count($args)) {
            throw new InvalidArgumentException("$source expects at least one Parser");
        }
    }
}
