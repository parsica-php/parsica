<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Parsica\Parsica\JSON\JSON as ParsicaJSON;
use Json as BaseMaxJson;

class JSONBench
{
    private string $data;

    function __construct()
    {
        $this->data = <<<JSON
{
    "name": "mathiasverraes/parsica",
    "type": "library",
    "alotoftext": [
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet",
        "Lorem Ipsum dolor sit amet"
    ],
    "alotmoretext": "Fuga iusto dolores ipsam. Qui excepturi veniam iste autem ducimus porro et voluptas. Veniam veniam ducimus cumque facere repudiandae corrupti sint quas. Cupiditate asperiores iure omnis dolores nihil asperiores qui quo. Assumenda quia iure deserunt deserunt. Perspiciatis velit quia et.\n\nExplicabo non dolores aut facere. Perferendis in est voluptate. Et laboriosam et autem voluptatum rem nam et aut. Voluptatem praesentium et earum fugit accusamus tempore consectetur natus. Beatae sunt nisi rerum blanditiis consequatur rerum ut.\n\nIure ipsa sit assumenda. Vitae nisi qui vero. Eveniet cum aliquam molestiae molestias. Nisi aut ea alias quo ea voluptatem. Minus ea mollitia quis.",
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
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_json_encode()
    {
        json_decode($this->data);
    }

    /**
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_Parsica_JSON()
    {
        $result = ParsicaJSON::json()->tryString($this->data);
    }


    /**
     * @Revs(10)
     * @Iterations(10)
     */
    public function bench_basemax_jpophp()
    {
        require_once(__DIR__.'/JPOPHP/JsonParser.php');
        (new JPOPHP\Json())->decode($this->data);
    }
}
