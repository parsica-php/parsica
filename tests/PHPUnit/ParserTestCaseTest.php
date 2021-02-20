<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Parsica\Parsica\PHPUnit;

use PHPUnit\Framework\TestCase;
use Parsica\Parsica\PHPUnit\ParserAssertions;

final class ParserTestCaseTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function strict_equality()
    {
        $this->assertEquals(1.23, "1.23",
            "A string and a float are equal in php");
        $this->assertNotSame(1.23, "1.23",
            "A string and a float are not the same in php");
        $this->assertSame(1.23, 1.23,
            "Primitives are compared by value");
        $this->assertEquals(new MyType(1.23), new MyType(1.23),
            "Weak equality works for objects");
        $this->assertNotSame(new MyType(1.23), new MyType(1.23),
            "...but value object instances with the same value do not have equality");
        $this->assertTrue((new MyType(1.23))->equals(new MyType(1.23)),
            "We can solve it with an equals() method, but the user doesn't always have "
            . "control of the types.");

        $this->assertTrue(true,
            "Therefore, we need something that will behave like assertSame for primitives, "
            . "like assertEquals for objects of the same type,"
            . "and fail for everything else.");

        $this->assertStrictlyEquals(1.23, 1.23);
        $this->assertStrictlyEquals(new MyType(1.23), new MyType(1.23));

        /*
        $this->assertStrictlyEquals(1.23, "1.23",
            "should fail");
        $this->assertStrictlyEquals("1.23", 1.23,
            "should fail");
        $this->assertStrictlyEquals(new MyType(1.23), new MyType(7.89),
            "should fail");
        */
    }

    /** @test */
    public function strictlyEquals_for_arrays()
    {
        $this->assertStrictlyEquals(
            [1, new MyType(5.0)],
            [1, new MyType(5.0)]
        );
    }

}

final class MyType
{
    private float $x;

    public function __construct(float $x)
    {
        $this->x = $x;
    }

    public function equals(MyType $other): bool
    {
        return $this->x === $other->x;
    }

}
