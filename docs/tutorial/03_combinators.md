---
title: Using Combinators
---


## Fluent interface

Many combinators come both as a standalone function, and as a method on the `Parser`object. They behave the same, and exist as a convenience for writing more readable code. Choosing one or the other will mostly depend on your usecase. 

The general rule is that `combinator($parserA, $parserB) ≡ $parserA->combinator($parserB)`, in other words, they are equivalent.

In the example below, the `sequence` and `optional` combinators are used as functions and as methods, and both parsers are fully equivalent.  

```php
<?php
$parser1 = sequence(
            optional(char('a')),
            char('b')
          );
$parser2 = char('a')->optional()
            ->sequence(char('b'));
```

Sometimes combinators have different names for the same behaviour: `$parserA->or($parserB) ≡ either($parserA, $parserB)`. In this case, the reason is partially because `or` is a reserved keyword in PHP, and partially because `either` reads better in this case. Some combinators have aliases, such as `Parser#sequence()` and `Parser#followedBy()`, again these exist purely for convenience.   

## Sequences

`sequence` is one of the most basic combinators you'll find. `sequence($parser1, $parser2)` means *"Try the first parser. If it fails, return the failure. If it succeeds, take the remaining input that was not consumed by `$parser1`, and try `$parser2`. Return the result of `$parser2`."*

It's important to understand that this drops whatever output `$parser1` produced. That's useful when you're only interested in what comes after `$parser1`. This example extracts a value that is prefixed by a string.

```php
<?php
$parser = sequence(string('My name is '), atLeastOne(alphaChar()));
$result = $parser->tryString("My name is Parsica");
assertEquals("Parsica", $result->output());
``` 

## Alternatives

@TODO

## Appending

@TODO

## Folding combinators

There are also combinators that extend the behaviour of others. For example, `choice` is a left fold over the `either` combinator, effectively turning it from a combinator that takes two arguments, to one that take n arguments. `choice($parser1, $parser2, $parser3, ...) ≡ $parser1->or($parser2)->or($parser3)->or...`

The same happens with the `assemble` combinator, which call appends all its arguments. `assemble($parser1, $parser2, $parser3, ...) ≡ $parser1->and($parser2)->and($parser3)->...`

In general, you should use the simplest form available, so if you only have two choices, favour `or` over `choice`. 
