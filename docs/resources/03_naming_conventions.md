---
title: Naming Conventions
sidebar_label: Naming Conventions 
---


## String and Character

PHP doesn't have a separate type for strings and characters, as opposed to some languages where string is defined as a list of characters. Still, as a convention in Parsica and its documentation, we generally use `'a'`, `'1'` (single quoted) to indicate a single character, and `"a"`, `"abc123"` (double quoted) to indicate a string.

We also use single quotes to indicate constant strings or symbols, such as `'STATUS_SUCCESS'`;


## Predicates

Predicates are either prefixed with `is` or suffixed with `pred`.

```php
<?php
$predicate = orPred(isEqual('5'), isEqual('6'));
assertTrue($predicate('6'));
```

## Character Parsers

A parser for a single character is always suffixed with `Char`, as in `digitChar()`. These always output a string.

## Case

Some parsers have case-insensitive versions. These are sufficed with 'I'.

```php
<?php
$parser = stringI('hello world'); 
$result = $parser->tryString("hElLO WoRlD"); 
assertEquals("hElLO WoRlD", $result->output());
```

