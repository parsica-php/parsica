<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

use Webmozart\Assert\Assert;

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