---
title: Building Blocks 
---

## Predicates

The simplest building block is a parser that only considers the first character of an input. If the character satisfies some condition, we consume it from the input. We could write that with some `if` statements and `substr` calls, but Parsica provides abstractions for that.

```php
<?php
$parser = satisfy(isEqual('a'));
$input = "abc";
$result = $parser->tryString($input);
assertEquals("a", $result->output());
assertEquals("bc", $result->remainder());
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
$result = $parser->tryString("ABC");
assertEquals("A", $result->output());
$result = $parser->tryString("abc");
assertEquals("a", $result->output());
```

Parsica provides various parsers for groups of characters, like `alphaNumChar`, `upperChar`, `punctuationChar`, `newline`, and `digitChar`. You can find them all listed in the API Reference.

```php
<?php
$parser = digitChar();
$result = $parser->tryString('123');
assertEquals('1', $result->output());
```

Note that even though we parsed a `digitChar`, the output is a string, not an int. That's because at this point, we're parsing characters. We'll talk about outputting other types than string later.


## Strings

For longer sequences of characters, you can use `string` and `stringI`. Keep in mind that `stringI`is not just case-insensitive, but also case-preserving. 

```php
<?php
$parser = stringI("parsica");
$result = $parser->tryString("PARSICA");
assertEquals("PARSICA", $result->output()); 
$result = $parser->tryString("pArSiCa");
assertEquals("pArSiCa", $result->output()); 
```
 
If you want the output to be consistent, you can use `map` to convert it.


```php
<?php
$parser = stringI("parsica")
    ->map(fn($output) => strtolower($output));
$result = $parser->tryString("pArSiCa");
assertEquals("parsica", $result->output()); 
```
 

## Other parsers

Parsica comes with a growing library of other useful parsers, such as numeric types, and spaces. Always make sure to check the API documentation to know what the type of a parser is (aka the tpye of the output that the parser will produce.) For example, parsers like `space`, `tab`, and `newline` all output strings containing the characters they matched. On the other hand, `skipSpace` will output `null`, no matter if it consumed spaces or not. This makes sense because the point is to ignore them, not use them.    

`skipSpace` consumes all kinds of space, whereas `skipHSpace` will stop consuming at newlines and carriage returns. They also come with two friends, `skipSpace1` and `skipHSpace1`, which expect at least on space to present.


 
 
