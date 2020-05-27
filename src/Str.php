<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Webmozart\Assert\Assert;

/**
 * String helper functions
 * @TODO replace by something existing?
 */
final class Str
{
    /**
     * Return the first character of a non-empty list
     */
    public static function head(string $s): string
    {
        Assert::minLength($s, 1);
        return $s[0];
    }

    public static function tail(string $s): string
    {
        return (strlen($s) > 1)
            ? substr($s, 1)
            : "";
    }
}