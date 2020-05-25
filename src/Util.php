<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;


function head(string $s): string
{
    return $s[0];
}

function tail(string $s): string
{
    return (strlen($s) > 1)
        ? substr($s, 1)
        : "";
}