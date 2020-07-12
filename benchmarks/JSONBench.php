<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Verraes\Parsica\JSON\JSON;

class JSONBench
{
    private string $data;

    function __construct()
    {
        $this->data = <<<JSON
{
    "name": "mathiasverraes/parsica",
    "type": "library",
    "description": "The easiest way to build robust parsers in PHP.",
    "keywords": [
        "parser",
        "parser-combinator",
        "parser combinator",
        "parsing"
    ]
}

JSON;

    }

    /**
     * @Revs(5)
     * @Iterations(3)
     */
    public function bench_Parsica_JSON()
    {
        $result = JSON::json()->tryString($this->data);
    }

    /**
     * @Revs(5)
     * @Iterations(3)
     */
    public function bench_json_encode()
    {
        json_decode($this->data);
    }
}
