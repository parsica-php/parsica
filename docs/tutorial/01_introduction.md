---
title: What are parser combinators?
---

Before you start, make sure you've had a look at the installation instructions.


## Parsers

```php
<?php
$parser = char(':')
            ->append(atLeastOne(punctuationChar()))
            ->label('smiley');
$result = $parser->tryString(':*{)'); 
echo $result->output() . " is a valid smiley!";
```


A parser is a function that takes some unstructured input (like a string) and turns it into structured output, that's easier to work with. This output could be as simple as a slightly better structured string, or an array, an object, up to a complete abstract syntax tree. You can then use this data structure for subsequent processing.

You're probably using parsers all the time, such as `json_decode()`. And even just casting a string to a float <sup>[footnote 1](#floatval)</sup> really is parsing. 

Parsica helps you build your own parsers, in a concise, declarative way. Behind the scenes it takes care of things like error handling, so you can focus on the parser itself. 


## Building a parser

There are many ways to build a parser for your own use case, ranging from formal grammars that get compiled into a parser, to regular expressions, to writing a parser entirely from scratch. They all have their own tradeoffs and limitations. 

One of the great benefits of the parser combinator style is that, once you get the hang of it, they're generally easier to write, understand, and maintain. You start from building blocks, such as `digitChar()`, which returns a function that parses a single digit.  

```php
<?php 
$parser = digitChar();
$input = "1. Write Docs";
$result = $parser->tryString($input);
$output = $result->output();
assertSame("1", $output);
assertIsString($output);
```

## Parser Combinators

Parser Combinators are functions (or methods) that combine parsers into new parsers. Instead of writing one big parser, we can now write smaller parsers and cleverly compose them into larger parsers. 

```php
<?php
$parser = char('a')->append(char('b'));
$result = $parser->tryString("abc");
$output = $result->output();
assertEquals("ab", $output);
```

```php
<?php
$parser = 
    collect(
        string("Hello")->thenIgnore(char(",")),
        string("world")->thenIgnore(char("!")),
    );
$result = $parser->tryString("Hello,world!");
$output = $result->output();
assertEquals(["Hello", "world"], $output);   
```

To make this work, we need a small change in our original definition of a parser.

> A parser is a function that takes some unstructured input (such as a string), and returns a more structured output, as well as the remaining unparsed part of the input.

This way, each parser function can parse a chunk of the input, and leave the remainder to another parser. The combinators take care of the heavy lifting: pass the input to the parser functions, pass the remainder to the next one, decide what to do with errors (eg, fail or backtrack or try another parser), ...   

We can inspect the remainder:

```php
<?php
$parser = sequence(char('a'), char('b'));
$result = $parser->tryString("abc");

assertEquals("b", $result->output());
assertEquals("c", $result->remainder());
 ```

So when we run our parser using `$parser->tryString($input)`, the `sequence()` combinator first tries to run `char('a')` on the input `"abc"`. If it succeeds, it takes the remainder `"bc"` and successfully runs `char('b')` on it and returns the result. That result consists of the output from the last parser `"b"`, and the remainder `"c"`.

In imperative code, it would look something like this:

```php
<?php
final class MyParser 
{
    public function try(string $input) : array 
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
    }
}
$parser = new MyParser();
$result = $parser->try("abc");

assertEquals('b', $result['output']);
assertEquals('c', $result['remainder']);
```

If you've been working in PHP long enough and have never used parser combinators, the code above may look more familiar for now. But imagine scaling that to parse anything from simple formats like credit card numbers, recursive structures like JSON or XML, or even entire programming languages like PHP. And that doesn't even include the code you'd need for performance, testing and debugging tooling, code reuse, and reporting on bad input. If you'd rather write `sequence(char('a'), char('b'))`, stick around.


### Footnotes

#### <a name="floatval">Note 1</a> 

```php
<?php
$v = floatval("1.23");
assertSame(1.23, $v); 
```

The above looks fine at first sight, but `floatval()` really isn't a very good parser.

```php
<?php
assertSame(0.0, floatval("abc"));
```

`floatval()` claims that the float of `"abc"` is `0`, which really should be an error. So you can only use `floatval` when you already know that the string doesn't contain anything non-float. Parsica can help you do that:

```php
<?php
$parser = float()->map(fn($v) => floatval($v));
try {
    // works: 
    $result = $parser->tryString("1.23");
    assertSame(1.23, $result->output());
 
    // throws a ParserHasFailed exception with message "Expected: float, got abc"
    $result = $parser->tryString("abc");
} catch (ParserHasFailed $e) {}
```
