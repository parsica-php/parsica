---
title: Building Blocks 
---

## Predicates

The simplest building block is a parser that only considers the first character of an input. If the character satisfies some condition, we consume it from the input. We could write that with some `if` statements and `substr` calls, but Parsica provides abstractions for that.

```php
<?php
$parser = satisfy(isEqual('a'));
$input = "abc";
$result = $parser->try($input);
assert($result->output() == "a");
assert($result->remainder() == "bc");
``` 

`isEqual('a')` is a predicate. If you call it with another argument, you get a boolean: `isEqual('a')('b') == false`.

`satisfy($predicate)` is a function returns a `Parser` object. You can think of it as a parser constructor. This object will do the heavy lifting of taking the first character of `$input`, and testing it with the predicate. 

Parsica comes with some useful predicates, including boolean and/or/not combinators: 

```php
<?php
$parser = satisfy(orPred(isDigit(), isWhitespace()));
```

## Character parsers

In practice, you may not need to use predicates and `satisfy` very often. The characters API provides commonly used parsers for single characters instead:

```php
<?php
$parser = char('a');
```

`char($x)` is defined as `satisfy(isEqual($x))` so the code above is equivalent to the first example. `charI()` is the case-insensitive version of `char()`. It preserves the case as is:

```php
<?php
$parser = charI('a');
$result = $parser->try("ABC");
assert($result->output() == "A");
$result = $parser->try("abc");
assert($result->output() == "a");
```

Parsica provides various parsers for groups of characters, like `alphaNumChar`, `uppercChar`, `punctuationChar`, `newline`, and `digitChar`. You can find them all listed in the API Reference. 


```php
<?php
$parser = digitChar('a');
$result = $parser->try('123');
assert($result->output() == "1");
assert(is_string($result->output()));
```

Note that even though we parsed a `digitChar`, the output is a string, not an int. That's because at this point, we're parsing characters. We'll talk about outputting other types than string later.
