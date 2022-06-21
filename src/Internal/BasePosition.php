<?php

namespace Parsica\Parsica\Internal;

interface BasePosition
{
    public static function initial(string $filename): BasePosition;
    public function pretty(): string;
    public function advance(string $parsed): BasePosition;
    public function filename(): string;
    public function line(): int;
    public function column(): int;
}
