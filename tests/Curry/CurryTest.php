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
    public function test_curry_without_params()
    {
        $simpleFunction = curry(function () { return 1; });
        $this->assertEquals(1, $simpleFunction());
    }

    public function test_curry_identity()
    {
        $identity = curry(array(new TestSubject(), 'identity'), 1);
        $this->assertEquals(1, $identity(1));
    }

    public function test_curry_identity_function()
    {
        $func = curry(function ($v) { return $v; }, 'test string');
        $this->assertEquals('test string', $func());
    }

    public function test_curry_args_identity_function()
    {
        $func = curry_args(function ($v) { return $v; }, ['test string']);
        $this->assertEquals('test string', $func());
    }

    public function test_curry_args_identity()
    {
        $identity = curry_args(array(new TestSubject(), 'identity'), array(1));
        $this->assertEquals(1, $identity(1));
    }

    public function test_curry_with_one_later_param()
    {
        $curriedOne = curry(array(new TestSubject(), 'add2'), 1);
        $this->assertInstanceOf('Closure', $curriedOne);
        $this->assertEquals(2, $curriedOne(1));
    }

    public function test_curry_with_two_later_param()
    {
        $curriedTwo = curry(array(new TestSubject(), 'add4'), 1, 1);
        $this->assertInstanceOf('Closure', $curriedTwo);
        $this->assertEquals(4, $curriedTwo(1, 1));
    }

    public function test_curry_args_with_two_later_param()
    {
        $curriedTwo = curry_args(array(new TestSubject(), 'add4'), array(1, 1));
        $this->assertInstanceOf('Closure', $curriedTwo);
        $this->assertEquals(4, $curriedTwo(1, 1));
    }

    public function test_curry_with_successive_calls()
    {
        $curriedTwo = curry(array(new TestSubject(), 'add4'), 1, 1);
        $curriedThree = $curriedTwo(1);
        $this->assertEquals(4, $curriedThree(1));
    }

    public function test_curry_right()
    {
        $divideBy10 = curry_right(array(new TestSubject(), 'divide2'), 10);
        $this->assertInstanceOf('Closure', $divideBy10);
        $this->assertEquals(10, $divideBy10(100));
    }

    public function test_curry_right_args()
    {
        $divideBy10 = curry_right_args(array(new TestSubject(), 'divide2'), array(10));
        $this->assertInstanceOf('Closure', $divideBy10);
        $this->assertEquals(10, $divideBy10(100));
    }

    public function test_curry_right_immediate()
    {
        $divide3 = curry_right(array(new TestSubject(), 'divide3'), 5, 2, 20);
        $this->assertEquals(2, $divide3());
    }

    public function test_curry_right_args_immediate()
    {
        $divide3 = curry_right_args(array(new TestSubject(), 'divide3'), array(5, 2, 20));
        $this->assertEquals(2, $divide3());
    }

    public function test_curry_left_immediate()
    {
        $divide3 = curry(array(new TestSubject(), 'divide3'), 20, 2, 4);
        $this->assertEquals(2.5, $divide3());
    }

    public function test_curry_three_times()
    {
        $divideBy5 = curry(array(new TestSubject(), 'divide3'), 100);
        $divideBy10And5 = $divideBy5(10);
        $this->assertEquals(2, $divideBy10And5(5));
    }

    public function test_curry_right_three_times()
    {
        $divideBy5 = curry_right(array(new TestSubject(), 'divide3'), 5);
        $divideBy10And5 = $divideBy5(10);
        $this->assertEquals(2, $divideBy10And5(100));
    }

    public function test_curry_right_args_three_times()
    {
        $divideBy5 = curry_right_args(array(new TestSubject(), 'divide3'), array(5));
        $divideBy10And5 = $divideBy5(10);
        $this->assertEquals(2, $divideBy10And5(100));
    }

    public function test_curry_using_func_get_args()
    {

        $fnNoArgs = function () { return func_get_args(); };
        $curried = curry($fnNoArgs);
        $curriedRight = curry_right($fnNoArgs);

        $this->assertEquals(array(), $fnNoArgs());
        $this->assertEquals(array(), $curried());
        $this->assertEquals(array(), $curriedRight());

        $this->assertEquals(array(1), $fnNoArgs(1));
        $this->assertEquals(array(1), $curried(1));
        $this->assertEquals(array(1), $curriedRight(1));

        $this->assertEquals(array(1, 2, 'three'), $fnNoArgs(1, 2, 'three'));
        $this->assertEquals(array(1, 2, 'three'), $curried(1, 2, 'three'));
        $this->assertEquals(array(1, 2, 'three'), $curriedRight(1, 2, 'three'));

        $fnOneArg = function ($x) { return func_get_args(); };
        $curried = curry($fnOneArg);
        $curriedRight = curry_right($fnOneArg);

        $this->assertEquals(array(1), $fnOneArg(1));
        $this->assertEquals(array(1), $curried(1));
        $this->assertEquals(array(1), $curriedRight(1));

        $this->assertEquals(array(1, 2, 'three'), $fnOneArg(1, 2, 'three'));
        $this->assertEquals(array(1, 2, 'three'), $curried(1, 2, 'three'));
        $this->assertEquals(array(1, 2, 'three'), $curriedRight(1, 2, 'three'));

        $fnTwoArgs = function ($x, $y) { return func_get_args(); };
        $curried = curry($fnTwoArgs);
        $curriedRight = curry_right($fnTwoArgs);

        $curriedOne = $curried(1);
        $curriedRightOne = $curriedRight(2);
        $curriedRightTwo = $curriedRight('three');

        $this->assertEquals(array(1, 2), $fnTwoArgs(1, 2));
        $this->assertEquals(array(1, 2), $curried(1, 2));
        $this->assertEquals(array(1, 2), $curriedRight(2, 1));

        $this->assertEquals(array(1, 2, 'three'), $fnTwoArgs(1, 2, 'three'));
        $this->assertEquals(array(1, 2, 'three'), $curried(1, 2, 'three'));
        $this->assertEquals(array(1, 2, 'three'), $curriedRight('three', 2, 1));

        $this->assertEquals(array(1, 2), $curriedOne(2));
        $this->assertEquals(array(1, 2), $curriedRightOne(1));

        $this->assertEquals(array(1, 2, 'three'), $curriedOne(2, 'three'));
        $this->assertEquals(array(1, 2, 'three'), $curriedRightTwo(2, 1));
    }

    public function test_curry_with_placeholders()
    {
        $minus = curry(function ($x, $y) { return $x - $y; });
        $decrement = $minus(__(), 1);

        $this->assertEquals(9, $decrement(10));

        $introduce = curry(function($name, $age, $job, $details = '') {
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
        $add = function($x, $y){ return $x + $y; };
        $sum = $reduce(__(), $add);

        $this->assertEquals(10, $sum(array(1, 2, 3, 4), 0));
    }

    public function test_rest()
    {
        $this->assertEquals(array(1), _rest(array(1, 1)));
        $this->assertEquals(array('a', 'b'), _rest(array(1, 'a', 'b')));
        $this->assertEquals(array(), _rest(array(1)));
        $this->assertEquals(array(), _rest(array()));
    }

    /**
     * @dataProvider provider_is_fullfilled
     */
    public function test_is_fullfilled($isFullfilled, $args, $callable)
    {
        $this->assertSame($isFullfilled, _is_fullfilled($callable, $args));
    }

    public function provider_is_fullfilled()
    {
        return array(
            array(false, array(), function ($a) {}),
            array(true, array(), function () {}),
            array(true, array(1), function ($a) {}),
            array(false, array(1), function ($a, $b) {}),
            array(false, array(1), array(new TestSubject(), 'add2')),
            array(true, array(1, 2), array(new TestSubject(), 'add2')),
            array(true, array('aaa', 'a'), 'strpos'),
        );
    }
}

class TestSubject
{
    public function identity($a) {
        return $a;
    }

    public function add2($a, $b) {
        return $a + $b;
    }

    public function divide2($a, $b) {
        return $a / $b;
    }

    public function divide3($a, $b, $c) {
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
