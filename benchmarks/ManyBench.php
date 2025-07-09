<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Parsica\Parsica\Parser;
use function Parsica\Parsica\any;
use function Parsica\Parsica\char;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\many;
use function Parsica\Parsica\map;
use function Parsica\Parsica\pure;
use function Parsica\Parsica\recursive;
use function Parsica\Parsica\satisfy;
use function Parsica\Parsica\takeWhile;

class ManyBench
{
    private string $data;

    function __construct()
    {
        $this->data = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
    }

    /**
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_takeWhile()
    {
        $result = takeWhile(fn(string $c): bool => $c === 'a')
            ->tryString($this->data);
    }

    /**
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_manySatisfy()
    {
        $result =  many(satisfy(fn(string $c): bool => $c === 'a'))
            ->tryString($this->data);
    }

    /**
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_manyChar()
    {
        $result = many(char('a'))
            ->tryString($this->data);
    }

    /**
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_oldManySatisfy()
    {
        $result = static::oldMany(satisfy(fn(string $c): bool => $c === 'a'))
            ->tryString($this->data);
    }

    /**
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_oldManyChar()
    {
        $result = static::oldMany(char('a'))
            ->tryString($this->data);
    }

    public static function oldMany(Parser $parser)
    {
        $rec = recursive();
        $rec->recurse(
            any(
                map(
                    collect($parser, $rec),
                    fn(array $o): array => array_merge([$o[0]], $o[1])
                ),
                pure([]),
            )
        );
        return $rec;
    }
}
