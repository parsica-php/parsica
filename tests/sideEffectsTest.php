<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\StringStream;
use function Parsica\Parsica\char;
use function Parsica\Parsica\emit;

final class sideEffectsTest extends TestCase
{
    /** @test */
    public function emit()
    {
        $cache = new Cache();
        $addToCache = fn($output) => $cache->add($output, $output);

        $parser = emit(char('a'), $addToCache);

        $input = "a";
        $parser->run(new StringStream($input));
        $this->assertEquals("a", $cache->get('a'));

        $input = "b";
        $parser->run(new StringStream($input));
        $this->assertNull($cache->get('b'));
    }
}

class Cache
{
    private $items = [];

    function add($key, $value)
    {
        $this->items[$key] = $value;
    }

    function get($key)
    {
        return array_key_exists($key, $this->items)
            ? $this->items[$key]
            : null;
    }
}
