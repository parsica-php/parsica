<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template T
 */
interface Alternative
{
    /**
     * @param Alternative<T> $other
     * @return Alternative<T>
     */
    public function alternative(Alternative $other) : Alternative;

    /**
     * @return Alternative<T>
     */
    public static function mempty() : Alternative;
}