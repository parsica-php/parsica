<?php declare(strict_types=1);
/**
 * This code is forked from https://github.com/matteosister/php-curry, which is abandoned. It could be integrated into
 * the rest of Parsica.
 */

namespace Parsica\Parsica\Curry;

/**
 * This class is created simply to define a special type
 * for the placeholder. As defining a constant, even
 * a random one, could collide with other values.
 * @psalm-immutable
 */
final class Placeholder
{
    public function __toString()  : string
    {
        return '__';
    }
}
