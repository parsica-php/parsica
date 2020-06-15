# Functional Paradigms

We use a number of paradigms from functional programming throughout the code. [We've chosen not to expose these to end users](design_goals.md). We list them here for anybody who's interested.

## Purity

Almost all code is pure and referentially transparent. [A notable exception](recursion.md) is the combo of `recursive()` and `Parser::recurse()`. The latter mutates a `Parser`. We constrained this so that you can't use the parser when it's not set up yet, and after calling `recurse()`, you can't call it again.

All functions that return parsers ar pure, and even though there are some instance methods on `Parser`, these are also pure.

```
$parser->combinator($otherParser);
// Is equivalent to
combinator($parsere, $otherParser);
```

In fact, very often there are both a function and an instance method for the same combinator.

## Types

There are no generics in PHP 7.4, but we use thee Psalm static typechecker to simulate some of it. The two type are really `Parser<T>` and `ParseResult<T>`, where `T` is the type of the resulting output in the case of a successful parse. 

## Either

`ParseResult<T>` is approximately an `Either<ParseFailure, ParseSuccess<T>>` type.  
## Maybe

`ParseResult<T>` is also double serving as a kind of `Maybe<T>`. You can think of a successful parseresult as `Just<T>`, and a discarded parse result as `Nothing`. 

## Functors

`ParseResult` and `Parser` are functors, using the `fmap` method. 

For `ParseResult`, the function is only applied to the output if `ParseResult::isSuccess()` is true, and ignored in other cases. 

Similarly, mapping over `Parser` is really mapping over the future `ParseResult`. 

## Monoids

`ParseResult<T>` is a monoid under the `ParseResult::append()` operation, when `T` is a monoid as well. `discard()` is the zero value.

`Parser<T>` is a monoid under the `Parser::append()`, when `T` is a monoid as well. `nothing()` is the zero value. 

## Monads

`Parser<T>` is a monad. 

- `sequence()` runs two parsers in sequence, dropping the result of the first one. Both parsers consume input. You may know this as `>>`. The type of sequence is `Parser<T> -> Parser<T2> -> Parser<T2>`.
- `bind()` sequentially composes a parser and a parser-constructing function, passing the output produced by the first parser as an argument to the second.  Both parsers consume input. You may know this as `>>=` or `flatmap`. Type: `Parser<T> -> (T -> Parser<T2>) -> Parser<T2>`.
- `pure()` is a parser that will always output its argument, no matter what the input was. Type: `T -> Parser<T>`.


### Laws

@TODO write tests

Left identity: 

`bind(pure($a), $f) ≡ pure($a)->bind($f) ≡ $f($a)` 

Right identity: 

`bind($parser, 'pure') ≡ $parser->bind('pure') ≡ $parser`	

Associativity:

`$parser->bind($f)->bind($g) ≡ $parser->bind(fn($x) (use $f, $g) => $f($x)->bind($g))`
