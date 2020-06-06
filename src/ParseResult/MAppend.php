<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

final class MAppend
{
    public static function mappend(ParseResult $r1, ParseResult $r2): ParseResult
    {
        switch (true) {
            case $r1->isSuccess() && $r2->isDiscarded():
            case $r1->isFail() && $r2->isSuccess():
            case $r1->isFail() && $r2->isFail():
            case $r1->isFail() && $r2->isDiscarded():
            case $r1->isDiscarded() && $r2->isDiscarded():
                return $r1;
            case $r1->isSuccess() && $r2->isFail():
            case $r1->isDiscarded() && $r2->isSuccess():
            case $r1->isDiscarded() && $r2->isFail():
                return $r2;
            case $r1->isSuccess() && $r2->isSuccess():
                return self::mappendSuccess($r1, $r2);
        }
    }

    private static function mappendSuccess(ParseResult $r1, ParseResult $r2) : ParseResult
    {
        $type1 = $r1->type();
        $type2 = $r2->type();
        if($type1!==$type2) throw new \Exception("Mappend only works for ParseResult<T> instances with the same type T, got ParseResult<$type1> and ParseResult<$type2>.");

        switch($type1) {
            case 'string':
                return succeed($r1->parsed() . $r2->parsed(), $r2->remaining());
            case 'array':
                return succeed(
                    array_merge(array_values($r1->parsed()), array_values($r2->parsed())),
                    $r2->remaining()
                );
            default:
                throw new \Exception("@TODO cannot mappend ParseResult<$type1>");
        }
    }


}