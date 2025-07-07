# php-curry

An implementation for currying in PHP

Currying a function means the ability to pass a subset of arguments to a function, and receive back another function that accepts the rest of the arguments. As soon as the last one is passed it gets back the final result.

Like this:

``` php
$adder = function ($a, $b, $c, $d) {
  return $a + $b + $c + $d;
};

$firstTwo = C\curry($adder, 1, 2);
echo $firstTwo(3, 4); // output 10

$firstThree = $firstTwo(3);
echo $firstThree(14); // output 20
```

Currying is a powerful (yet simple) concept, very popular in other, more purely functional languages. In haskell for example, currying is the default behavior for every function.

In PHP we still need to rely on a wrapper to simulate the behavior

### Right to left

It's possible to curry a function from left (default) or from right.

``` php
$divider = function ($a, $b) {
    return $a / $b;
};

$divide10By = C\curry($divider, 10);
$divideBy10 = C\curry_right($divider, 10);

echo $divide10By(10); // output 1
echo $divideBy10(100); // output 10
```

### Optional parameters

Optional parameters and currying do not play very nicely together. This library excludes optional parameters by default.

``` php
$haystack = "haystack";
$searches = ['h', 'a', 'z'];
$strpos = C\curry('strpos', $haystack); // You can pass function as string too!
var_dump(array_map($strpos, $searches)); // output [0, 1, false]
```

But strpos has an optional $offset parameter that by default has not been considered.

If you want to take this optional $offset parameter into account you should "fix" the curry to a given length.

``` php
$haystack = "haystack";
$searches = ['h', 'a', 'z'];
$strpos = C\curry_fixed(3, 'strpos', $haystack);
$finders = array_map($strpos, $searches);
var_dump(array_map(function ($finder) {
    return $finder(2);
}, $finders)); // output [false, 5, false]
```

*curry_right* has its own fixed version named *curry_right_fixed*

### Placeholders

The function `__()` gets a special placeholder value used to specify "gaps" within curried functions, allowing partial application of any combination of arguments, regardless of their positions.

```php
$add = function($x, $y)
{ 
	return $x + $y; 
};
$reduce = C\curry('array_reduce');
$sum = $reduce(C\__(), $add);

echo $sum([1, 2, 3, 4], 0); // output 10
```

**Notes**:

- Placeholders should be used only for required arguments.

- When used, optional arguments must be at the end of the arguments list.
