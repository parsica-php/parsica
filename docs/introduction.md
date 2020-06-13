# Introduction

## Parsers

A parser is a function that takes some unstructured input (like a string) and turns it into structured output. This output could be as simple as a slightly better structured string, or an array, an object, up to a complete abstract syntax tree. You can then use this data structure for subsequent processing.

You're probably using parsers all the time, such as `json_decode()`. And even just casting a string to a float<sup>[1](#floatval)</sup> is parsing. 

```php
<?php
$v = floatval("1.23");
// $v is now a float 1.23
```

## Building a parser

There are many ways to build a parser for your own use case, ranging from formal grammars that get compiled into a parser, to regular expressions, to writing a parser entirely from scratch. They all have their own tradeoffs and limitations. 

One of the great benefits of parser combinators is that, once you know how, they're very easy to write, understand, and maintain. You start from building blocks, such as `digit()`, which returns a function that parses a single digit.  

```php
<?php 
$parser = digit();
$input = "1. Write Docs";
$result = $parser->try($input);
$output = $result->output();
// $output is a string "1"
```

## Parser Combinators

Parser Combinators are functions (or methods) that combine parsers into new parsers. Instead of writing one big parser, we can now write smaller parsers and cleverly compose them into larger parsers. 

```php
<?php
$parser = char('a')->mappend(char('b'));
$result = $parser->try("abc");
$output = $result->output();
// $output is "b"
```

```php
<?php
$parser = 
    collect(
        string("Hello"), 
        ignore(char(",")),
        skipSpace(),
        string("world"),
        ignore(char("!")),
    );
$result = $parser->try("Hello, world!");
$output = $result->output();
// $output is ["Hello", "World"];   
```

To make this work, we need a small change in our original definition of a parser.

> A parser is a function<sup>[2](#object)</sup> that takes some unstructured input (such as a string), and returns a more structured output, as well as the remaining unparsed part of the input.

This way, each parser function can parse a chunk of the input, and leave the remainder to another parser. The combinators deal with executing all the parser they combined. 

We can inspect the remainder:

```php
<?php
$parser = seq(char('a'), char('b'));
$result = $parser->try("abc");
$output = $result->output(); 
// $output is "b"
assert($output === "b");
$remainder = $result->remainder();
// $remainder is "c"
 ```

So when we run our parser using `$parser->try($input)`, the sequence combinator `seq()` first tries to run `char('a')` on the input `"abc"`. If it succeeds, it takes the remainder `"bc"` and successfully runs `char('b')` on it and returns the result. That result consists of the output from the last parser `"b"`, and the remainder `"c"`.

In imperative code, it would have looked something like this:

```php
<?php
$input = "abc";
$myParser = function (string $input): array
{
    $output1 = substr($input, 0, 1); // "a"
    if ($output1 == 'a') {
        $remainder1 = substr($input, 1); // "bc"
        $output2 = substr($remainder1, 0, 1); // "b"
        if ($output2 == 'b') {
            $remainder2 = substr($remainder1, 1); // "c"
        } else {
            throw new Exception("Parser failed");
        }
    } else {
        throw new Exception("Parser failed");
    }
    return ['output' => $output2, 'remainder' => $remainder2];
};
$result = $myParser($input);
// $result is ['output' => 'b', 'remainder' => "c"]
```

If you've been working in PHP long enough and have never used parser combinators, the code above may look more familiar for now. But imagine scaling that to parse anything from formats like credit card numbers, recursive structures like JSON or XML, or even entire programming languages like PHP. And that doesn't even include the code you'd need for performance, testing and debugging tooling, code reuse, and reporting on bad input. If you'd rather write `char('a')->followedBy(char('b'))`, stick around.



### Footnotes

#### <a name="floatval">Note 1</a> 

On it's own, `floatval()` isn't a very good parser.

```php
<?php
echo  floatval("abc");
// 0
```

`floatval()` claims that the float of `"abc"` is `0`, which really should be an error. So you can only use `floatval` when you already know that the string doesn't contain anything non-float. This library can help you do that:


```php
<?php
$parser = float()->fmap('floatval');
try {
    $result = $parser->try("abc"); 
} catch (ParseFailure $e) {
    // throws an exception
}
```



#### <a name="object">Note 2</a> 

In our case, functions like `char('a')` return a `Parser` object with a method `try($input)`, so strictly speaking, `try` is the parser.  
