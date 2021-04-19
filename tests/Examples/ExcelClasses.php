<?php

namespace Tests\Parsica\Parsica\Examples;

class Cell
{
    private $col;
    private $row;

    function __construct($col, $row)
    {
        $this->col = $col;
        $this->row = $row;
    }
}
class Range
{
    private Cell $from;
    private Cell $to;

    function __construct(Cell $from, Cell $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
}
class Intersection
{
    private Range $l;
    private Range $r;

    function __construct(Range $l, Range $r)
    {
        $this->l = $l;
        $this->r = $r;
    }
}
class Sum
{
    private Intersection $intersection;

    function __construct(Intersection $intersection)
    {
        $this->intersection = $intersection;
    }
}
class Ampersand
{
    private Cell $l;
    private Cell $r;

    function __construct(Cell $l, Cell $r)
    {
        $this->l = $l;
        $this->r = $r;
    }
}
