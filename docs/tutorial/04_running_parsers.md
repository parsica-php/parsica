---
title: Running Parsers
---

There are different ways of running your parser on an input. 

## try() and tryString() 

Most of the time, you'll want to use `try`. It will run the parser on an input `Stream`, return a `Succeed` (which implements `ParseResult`) on success, and throw a `ParserHasFailed` exception if the input can't successfully be parsed. 

The `Stream` type generalises over a different ways of providing input. The simplest implementation is `StringStream`. This is really a wrapper around a PHP multibyte string. 

(In v0.6.0, `StringStream` is also the _only_ implementation of `Stream`, but this will change.)

`ParseResult` has an `output()` method, which has the type `T` for a `Parser<T>` (see [Mapping to Objects](mapping_to_objects)). It also has a `remainder()` method, which gives you the part of the input that wasn't consumed by the parser.
 
`ParserHasFailed` has the usual `Exception` methods. It also gives you access to the `Fail implements ParseResult` object. This contains all the relevant information about the failure, such as `expected()`, `got()`, and `position()`.  
 
```php
<?php
$parser = string('hello');

$result = $parser->try(new StringStream("hello world"));
// Or, use tryString(string), which is an alias of try(StringStream):
$result = $parser->tryString("hello world");

echo $result->output(); // "hello"
echo $result->remainder(); // StringStream(" world")

// Now let's make it fail
try {
    $result = $parser->tryString("hi world");
} catch(ParserHasFailed $e) {
    $result = $e->parseResult();
    echo $result->expected(); // "string(hello)"
    echo $result->got(); // StringStream("hi world")
    $position = $result->position(); // A Position object containing the line number,
                                     // column, and filename where the failure happened
}
```

## run()

`run` is mostly intended for internal use. 

The main difference between `run` and `try` is that `run` doesn't throw exceptions when parsing an input fails. (It might throw exceptions if your parser itself is incorrectly defined.) Instead, you'll always get a `ParseResult`, and you can inspect it with the same methods as above. You'll also get `isSuccess` and `isFail`, so you know what you're dealing with.

```php
<?php
$parser = string("hello");
$result = $parser->run(new StringStream("some input"));
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
$result1 = $parser1->run(new StringStream("hello world"));
$parser2 = string("world");
$result2 = $result1->continueWith($parser2);
```

`continueWith` takes another parser, and uses it to parse the remainder of the of the result. You may have noticed we didn't check for `isSuccess`. That's becasue we don't need to. `continueWith` is smart; if `$parser1` fails, trying to continue parsing on the result will not have any effect. In fact, the example above will fail, because `$parser1` doesn't take into account the space between "hello" and "world".
    
