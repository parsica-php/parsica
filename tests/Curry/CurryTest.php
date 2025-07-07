<?php declare(strict_types=1);
/**
 * This code is forked from https://github.com/matteosister/php-curry, which is abandoned. It could be integrated into
 * the rest of Parsica.
 */

namespace Tests\Parsica\Parsica\Curry;

use PHPUnit\Framework\TestCase;
use function Parsica\Parsica\Curry\__;
use function Parsica\Parsica\Curry\_is_fullfilled;
use function Parsica\Parsica\Curry\_rest;
use function Parsica\Parsica\Curry\curry;
use function Parsica\Parsica\Curry\curry_args;
use function Parsica\Parsica\Curry\curry_right;
use function Parsica\Parsica\Curry\curry_right_args;

final class CurryTest extends TestCase
{
    /**
     * @test
     */
    public function curry_without_params()
    {
        $simpleFunction = curry(function () {
            return 1;
        });
        $this->assertEquals(1, $simpleFunction());
    }

    /**
     * @test
     */
    public function curry_identity()
    {
        $identity = curry([new TestSubject(), 'identity'], 1);
        $this->assertEquals(1, $identity(1));
    }

    /**
     * @test
     */
    public function curry_identity_function()
    {
        $func = curry(function ($v) {
            return $v;
        }, 'test string');
        $this->assertEquals('test string', $func());
    }

    /**
     * @test
     */
    public function curry_with_one_later_param()
    {
        $curriedOne = curry([new TestSubject(), 'add2'], 1);
        $this->assertInstanceOf('Closure', $curriedOne);
        $this->assertEquals(2, $curriedOne(1));
    }

    /**
     * @test
     */
    public function curry_with_two_later_param()
    {
        $curriedTwo = curry([new TestSubject(), 'add4'], 1, 1);
        $this->assertInstanceOf('Closure', $curriedTwo);
        $this->assertEquals(4, $curriedTwo(1, 1));
    }

    /**
     * @test
     */
    public function curry_with_successive_calls()
    {
        $curriedTwo = curry([new TestSubject(), 'add4'], 1, 1);
        $curriedThree = $curriedTwo(1);
        $this->assertEquals(4, $curriedThree(1));
    }

    /**
     * @test
     */
    public function curry_right()
    {
        $divideBy10 = curry_right([new TestSubject(), 'divide2'], 10);
        $this->assertInstanceOf('Closure', $divideBy10);
        $this->assertEquals(10, $divideBy10(100));
    }

    /**
     * @test
     */
    public function curry_right_immediate()
    {
        $divide3 = curry_right([new TestSubject(), 'divide3'], 5, 2, 20);
        $this->assertEquals(2, $divide3());
    }

    /**
     * @test
     */
    public function curry_left_immediate()
    {
        $divide3 = curry([new TestSubject(), 'divide3'], 20, 2, 4);
        $this->assertEquals(2.5, $divide3());
    }

    /**
     * @test
     */
    public function curry_three_times()
    {
        $divideBy5 = curry([new TestSubject(), 'divide3'], 100);
        $divideBy10And5 = $divideBy5(10);
        $this->assertEquals(2, $divideBy10And5(5));
    }

    /**
     * @test
     */
    public function curry_right_three_times()
    {
        $divideBy5 = curry_right([new TestSubject(), 'divide3'], 5);
        $divideBy10And5 = $divideBy5(10);
        $this->assertEquals(2, $divideBy10And5(100));
    }

    /**
     * @test
     */
    public function curry_using_func_get_args()
    {

        $fnNoArgs = function () {
            return func_get_args();
        };
        $curried = curry($fnNoArgs);
        $curriedRight = curry_right($fnNoArgs);

        $this->assertEquals([], $fnNoArgs());
        $this->assertEquals([], $curried());
        $this->assertEquals([], $curriedRight());

        $this->assertEquals([1], $fnNoArgs(1));
        $this->assertEquals([1], $curried(1));
        $this->assertEquals([1], $curriedRight(1));

        $this->assertEquals([1, 2, 'three'], $fnNoArgs(1, 2, 'three'));
        $this->assertEquals([1, 2, 'three'], $curried(1, 2, 'three'));
        $this->assertEquals([1, 2, 'three'], $curriedRight(1, 2, 'three'));

        $fnOneArg = function ($x) {
            return func_get_args();
        };
        $curried = curry($fnOneArg);
        $curriedRight = curry_right($fnOneArg);

        $this->assertEquals([1], $fnOneArg(1));
        $this->assertEquals([1], $curried(1));
        $this->assertEquals([1], $curriedRight(1));

        $this->assertEquals([1, 2, 'three'], $fnOneArg(1, 2, 'three'));
        $this->assertEquals([1, 2, 'three'], $curried(1, 2, 'three'));
        $this->assertEquals([1, 2, 'three'], $curriedRight(1, 2, 'three'));

        $fnTwoArgs = function ($x, $y) {
            return func_get_args();
        };
        $curried = curry($fnTwoArgs);
        $curriedRight = curry_right($fnTwoArgs);

        $curriedOne = $curried(1);
        $curriedRightOne = $curriedRight(2);
        $curriedRightTwo = $curriedRight('three');

        $this->assertEquals([1, 2], $fnTwoArgs(1, 2));
        $this->assertEquals([1, 2], $curried(1, 2));
        $this->assertEquals([1, 2], $curriedRight(2, 1));

        $this->assertEquals([1, 2, 'three'], $fnTwoArgs(1, 2, 'three'));
        $this->assertEquals([1, 2, 'three'], $curried(1, 2, 'three'));
        $this->assertEquals([1, 2, 'three'], $curriedRight('three', 2, 1));

        $this->assertEquals([1, 2], $curriedOne(2));
        $this->assertEquals([1, 2], $curriedRightOne(1));

        $this->assertEquals([1, 2, 'three'], $curriedOne(2, 'three'));
        $this->assertEquals([1, 2, 'three'], $curriedRightTwo(2, 1));
    }

    /**
     * @test
     */
    public function curry_with_placeholders()
    {
        $minus = curry(function ($x, $y) {
            return $x - $y;
        });
        $decrement = $minus(__(), 1);

        $this->assertEquals(9, $decrement(10));

        $introduce = curry(function ($name, $age, $job, $details = '') {
            return "{$name}, {$age} years old, is a {$job} {$details}";
        });

        $introduceDeveloper = $introduce(__(), __(), 'Developer');
        $this->assertEquals("Foo, 20 years old, is a Developer ", $introduceDeveloper('Foo', 20));

        $introduceOld = $introduce(__(), 99, __());
        $this->assertEquals("Foo, 99 years old, is a Developer and Cooker as well", $introduceOld('Foo', 'Developer', 'and Cooker as well'));

        $introduceSkipName = $introduce(__());
        $introduceSkipJob = $introduceSkipName(99, __());

        $this->assertEquals("Foo, 99 years old, is a Cooker ", $introduceSkipJob('Foo', 'Cooker'));
        $this->assertEquals("Foo, 99 years old, is a Cooker yumm !", $introduceSkipJob('Foo', 'Cooker', 'yumm !'));

        $reduce = curry('array_reduce');
        $add = function ($x, $y) {
            return $x + $y;
        };
        $sum = $reduce(__(), $add);

        $this->assertEquals(10, $sum([1, 2, 3, 4], 0));
    }

    /**
     * @test
     */
    public function rest()
    {
        $this->assertEquals([1], _rest([1, 1]));
        $this->assertEquals(['a', 'b'], _rest([1, 'a', 'b']));
        $this->assertEquals([], _rest([1]));
        $this->assertEquals([], _rest([]));
    }

    /**
     * @test
     * @dataProvider provider_is_fullfilled
     */
    public function is_fullfilled($isFullfilled, $args, $callable)
    {
        $this->assertSame($isFullfilled, _is_fullfilled($callable, $args));
    }

    public function provider_is_fullfilled()
    {
        return [[false, [], function ($a) {
        }], [true, [], function () {
        }], [true, [1], function ($a) {
        }], [false, [1], function ($a, $b) {
        }], [false, [1], [new TestSubject(), 'add2']], [true, [1, 2], [new TestSubject(), 'add2']], [true, ['aaa', 'a'], 'strpos'],];
    }
}

final class TestSubject
{
    public function identity($a)
    {
        return $a;
    }

    public function add2($a, $b)
    {
        return $a + $b;
    }

    public function divide2($a, $b)
    {
        return $a / $b;
    }

    public function divide3($a, $b, $c)
    {
        return $a / $b / $c;
    }

    public function add3($a, $b, $c)
    {
        return $a + $b + $c;
    }

    public function add4($a, $b, $c, $d)
    {
        return $a + $b + $c + $d;
    }
}
