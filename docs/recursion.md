# Recursion 

Often we want to parse arbitrarily nested structures. Arrays, JSON, XML are such example. To do that, we need to be able to pass the parser to itself. Because of a limitation in PHP, we cannot pass a value around before it is created. The solution is to split this in two steps: create a placeholder for a recursive parser, and then define the parser in terms of itself. 

## Example

We need to parse nested pairs such as `[1,[2,[3,4]]]`. The structure repeats itself, every item in the pair can be either a digit or another pair. 

We cannot write this:

```
<?php
$pair = collect(
    ignore(char('[')),
    digit()->or($pair),
    ignore(char(',')),
    digit()->or($pair),
    ignore(char(']')),
);
```

The above results in "Undefined variable: pair" because we're trying to use `$pair` before it's defined. Instead, we need to mark the parser as `recursive` in a first step, and then define how the parser should `recurse`: 

```php
<?php
// Create a recursive parser first
$pair = recursive();

// Then define the parser
$pair->recurse(
    collect(
        ignore(char('[')),
        digit()->or($pair),
        ignore(char(',')),
        digit()->or($pair),
        ignore(char(']')),
    )
);

$result = $pair->run("[1,[2,[3,4]]]");
assert($result->output() == [1, [2, [3, 4]]]);
```

It's possible to nest multiple recursive parsers. Simply initialise them all first using  `recursive()` and then define them in terms of each other:

```php
<?php
$curlyPair = recursive();
$squarePair = recursive();

$anyPair = $curlyPair->or($squarePair);

$curlyPair->recurse(
    collect(
        ignore(char('{')),
        digit()->or($anyPair),
        ignore(char(',')),
        digit()->or($anyPair),
        ignore(char('}')),
    )
);

$squarePair->recurse(
    collect(
        ignore(char('[')),
        digit()->or($anyPair),
        ignore(char(',')),
        digit()->or($anyPair),
        ignore(char(']')),  
    )
);

$result = $anyPair->run("[1,[2,[3,4]]]");
assert($result->output() == [1, [2, [3, 4]]]);
$result = $anyPair->run("{1,{2,{3,4}}}");
assert($result->output() == [1, [2, [3, 4]]]);
```

Note that when you initialize a parser with `recursive()`, it is in fact mutable, and the `recurse()` method mutates it. All parsers are immutable, and this is the only exception. After calling `recurse()`, the parser is immutable again and behaves just like any other parser.