<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\PHPUnit;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;

final class ParserTestCaseTest extends ParserTestCase
{
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
            ."control of the types.");

        $this->assertTrue(true,
            "Therefore, we need something that will behave like assertSame for primitives, "
            ."like assertEquals for objects of the same type,"
            ."and fail for everything else.");

        $this->assertStrictlyEquals(1.23, 1.23);
        $this->assertStrictlyEquals(new MyType(1.23), new MyType(1.23));

        /*
        $this->assertStrictlyEquals(1.23, "1.23",
            "should fail");
        $this->assertStrictlyEquals("1.23", 1.23,
            "should fail");
        $this->assertStrictlyEquals(new MyType(1.23), new MyType(7.89),
            "should fail");
        $this->assertStrictlyEquals([new MyType(1.23)], [new MyType(1.23)],
            "We'd probably want this to pass?");
        */
    }

    /** @test */
    public function strictlyEquals_for_arrays()
    {
        throw new \Exception("@todo nto implemented");

    }


}

final class MyType
{
    private float $x;

    function __construct(float $x)
    {
        $this->x = $x;
    }

    function equals(MyType $other): bool
    {
        return $this->x === $other->x;
    }

}