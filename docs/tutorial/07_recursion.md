---
title: Recursion
---

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
    between(
        char('['),
        char(']'),
        collect(
            digitChar()->or($pair)
                ->thenIgnore(char(',')),
            digitChar()->or($pair)
        )
    ),
);

$result = $pair->tryString("[1,[2,[3,4]]]");
assertSame(['1', ['2', ['3', '4']]], $result->output());
```

It's possible to nest multiple recursive parsers. Simply initialise them all first using  `recursive()` and then define them in terms of each other:

```php
<?php
$curlyPair = recursive();
$squarePair = recursive();
$anyPair = $curlyPair->or($squarePair);

$inner = collect(
     digitChar()->or($anyPair)
         ->thenIgnore(char(',')),
     digitChar()->or($anyPair)
 );

$curlyPair->recurse(
    between(char('{'), char('}'), $inner),
);

$squarePair->recurse(
    between(char('['), char(']'), $inner),

);

$mixed = "{1,[2,{3,4}]}";
$result = $anyPair->tryString($mixed);
assertSame(['1', ['2', ['3', '4']]], $result->output());
```

Note that when you initialize a parser with `recursive()`, it is in fact mutable, and the `recurse()` method mutates it. All parsers are immutable, and this is the only exception. After calling `recurse()`, the parser is immutable again and behaves just like any other parser.

## Using recusion to avoid loops

Let's say we want to parse the character `'a'` at least one time, so that `"aaab"` outputs `"aaa"`, but `"bbb"` fails. Imperatively, you could solve this by running the `char('a')` parser in a while loop, and stop on the first failure. We can express it more concisely with recursion though: 

1. Start by parsing `char('a')`.
2. Append another `char('a')`, but this second one is `optional()`.
3. Append another `optional(char('a'))`
4. Notice the similarity between the first two steps. This suggest an opportunity for recursion. 
5. Wrap our `char('a')->append(optional(char('a')))` in a `recurse()` parser. 
6. Replace the second `char('a')` by the recursive parser.

The end result looks like this:

```php
<?php
$rec = recursive();
$rec->recurse(char('a')->append(optional($rec)));
$result = $rec->tryString("aaab");
assertEquals("aaa", $result->output());
```

In fact the code above is how the `atLeastOne()` combinator works, so you can simplify that code by writing this:

```php
<?php
$parser = atLeastOne(char('a'));
$result = $parser->tryString("aaab");
assertEquals("aaa", $result->output());
```


