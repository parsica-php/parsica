---
title: Performance
sidebar_label: Performance
---


At the time of writing, no effort has been made to measure the performance of Parsica. That doesn't mean it's slow; it means that we don't know yet. If you're going to use this on large amounts of data, do some profiling yourself first. Compute == carbon, and we'd like to keep this planet a little longer. You can help by contributing your profiling and optimisations. 
 
We have some ideas that will allow us to make it very efficient, and we intend to do that before getting to a 1.0 release.


## XDebug

Turn off XDebug, as it will make things much slower. If you do turn on XDebug, you may get `Maximum function nesting level of '256' reached, aborting!`. Increase the nesting level until the error goes away, either in code or in `php.ini`:

```php
<?php
ini_set('xdebug.max_nesting_level', '1024');
```

```ini
xdebug.max_nesting_level=1024
```

## Recursion

If you encounter a "Maximum function nesting level" error, the more likely problem is that you're building a recursive parser incorrectly. Have a look at the documentation page about recursion to learn more.


## Performance tips

Below we'll list some approaches to improve performance. 

The actual difference in performance depends on many factors, so measure your parsers' performance to know if it is actually faster.

### Reusing parsers is faster than rebuilding them

Storing parsers in a variable or property is often faster than rebuilding them. Compare the these two equivalent parsers:

```php
<?php
$slow = between(
    choice(char('"'), char("'")),
    choice(char('"'), char("'")),
    atLeastOne(alphaNumChar())
);

$quote = choice(char('"'), char("'"));
$fast = between(
    $quote,
    $quote,
    atLeastOne(alphaNumChar())
);
```

### Use predicates over higher level combinators

Often, a combinator may be replaced with lower level combinators to get the same result faster. For example, the following parsers are equivalent, but the second one is a lot faster:

```php
<?php
$somePredicate = isDigit();
$slow = zeroOrMore(satisfy($somePredicate));
$fast = takeWhile($somePredicate);
```

The reason is that `$slow` reads one token at a time, and then appends it to the previous tokens. `$fast` on the other hand, reads all the tokens until `$predicate` fails, and then returns them all at once. 

## Backtracking is slower

If your parser parses a long input, only to need to backtrack the whole thing when it fails, it's going to be slow. A better alternative is to organise your usage of choice in a way that only small chunks of the input need to be backtracked. 

```php
<?php
$parser = choice(
    atLeastOne(alphaChar())->thenEof(), 
    atLeastOne(alphaNumChar())->thenEof()
);
$result = $parser->tryString("abc123");
```
 
In this example, the choice parser parses "abc", fails on "1", backtracks, and then parses all of "abc123". If we switch the two parsers inside the choice parser, we are more likely to reach the end of the input without doing any backtracking.    

