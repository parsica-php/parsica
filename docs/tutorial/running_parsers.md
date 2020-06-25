---
title: Running Parsers
---

There ar two ways of running your parser on an input. 

## Try

Most of the time, you'll want to use `try`. It will run the parser, return a `ParseResult` on success, and throw a `ParserFailure` exception if the input can't successfully be parsed. 

`ParseResult` has an `output()` method, which has the type `T` for a `Parser<T>` (see [Mapping to Objects](mapping_to_objects)). It also has a `remainder()` method, which gives you the part of the input that wasn't consumed by the parser.
 
`ParserFailure` has the usual `Exception` methods, and  
 
```php
<?php
$parser = string('hello');
try {
    $result = $parser->try("hello world");
    echo $result->output(); // "hello"
    echo $result->remainder(); // " world"
    $result = $parser->try("hi world");
} catch(ParserFailure $e) {
    echo $e->expected(); // "string(hello)"
    echo $e->got(); // "hi world"
}
```

## Run

`run` is mostly intended for internal use. 

The main difference between `run` and `try` is that `run` doesn't throw exceptions when parsing an input fails. (It might throw exceptions if your parser itself is incorrectly defined.) Instead, you'll always get a `ParseResult`, and you can inspect it with the same methods as above. You'll also get `isSuccess` and `isFail`, so you know what you're dealing with.

```php
<?php
$parser = string("hello");
$result = $parser->run("some input");
if($result->isSuccess()) {
    echo $result->output();
    echo $result->remainder();
} elseif ($result->isFail()) {
    echo $result->expected();
    echo $result->got();
}
``` 

## Continue with a result

Using `run` instead of `try` is a good choice when you want to do something with the result, such as:

- Building your own combinators
- Interacting with `ParseResult` while in the middle of a parse flow

To do that, `ParseResult` lets you continue parsing:

```php
<?php
$parser1 = string("hello");
$result1 = $parser1->run("hello world");
$parser2 = string("world");
$result2 = $result1->continueWith($parser2);
```

`continueWith` takes another parser, and uses it to parse the remainder of the of the result. You may have noticed we didn't check for `isSuccess`. That's becasue we don't need to. `continueWith` is smart; if `$parser1` fails, trying to continue parsing on the result will not have any effect. In fact, the example above will fail, because `$parser1` doesn't take into account the space between "hello" and "world".
    
