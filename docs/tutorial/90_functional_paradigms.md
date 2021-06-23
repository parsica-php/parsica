---
title: Functional Paradigms
sidebar_label: Functional Paradigms
---

Internally, Parsica is designed using paradigms from functional programming.  We list them here for anybody who's interested in FP, but you don't need to know them to work with Parsica. 

Throughout this document, `$parser1 ≡ $parser2` means that you can swap `$parser1` with `$parser2` and vice-versa, and it will not affect the outcome of your program.

## Purity

Almost all the code is pure and referentially transparent. [A notable exception](recursion) is the combo of `recursive()` and `Parser::recurse()`. The latter mutates a `Parser`. We constrained this so that you can't use the parser when it's not set up yet, and after calling `recurse()`, you can't call it again. So not strictly pure, but close enough not to matter much in practice.

The combinators are all pure. Some combinators are implemented as instance methods on `Parser`, but these are also pure. You can think of them as functions that take `$this` as the first argument.

```
$parser1->combinator($parser2) 
    ≡ combinator($parser1, $parser2)
```

In fact, very often there are both a function and an instance method for the same combinator, where one is an alias for the other.

## Types

There are no generics in PHP 7.4, but we use the Psalm static typechecker to simulate some of it. The two type are really `Parser<T>` and `ParseResult<T>`, where `T` is the type of the resulting output in the case of a successful parse. 

## Either

`ParseResult<T>` is approximately an `Either<ParseFailure, ParseSuccess<T>>` type.  

## Functors

`ParseResult` and `Parser` are functors, using the `map` method. 

For `ParseResult`, the function is only applied to the output if `ParseResult::isSuccess()` is true, and ignored in other cases. 

Similarly, mapping over `Parser` is really mapping over the future `ParseResult`. 

## Monoids

`ParseResult<T>` is a monoid under the `ParseResult::append()` operation, when `T` is a monoid as well. `discard()` is the zero value.

`Parser<T>` is a monoid under the `Parser::append()`, when `T` is a monoid as well. `nothing()` is the zero value. 

### Laws


#### Identity

```
$parser->append(nothing()) ≡ $parser
```

```
nothing()->append($parser) ≡ $parser
```

#### Associativity

```
$p1->append($p2)->append($p3) 
    ≡ $p1->append($p2->append($p3))
```

## Applicative Functors

`Parser<T>` is an applicative functor.

- `pure()` is a parser that will always output its argument, no matter what the input was. Type: `T -> Parser<T>`.
- `apply()` is sequential application, aka `<*>`. `pure($callable)->apply($parser)` is a parser that applies `$callable` to the output of `$parser`. It works for callables with multiple arguments, if the callable is curried: `pure(curry($callable))->apply($p1)->apply($p2)`. We used [matteosister/php-curry](https://github.com/matteosister/php-curry) to test this, but any method for currying functions should work.
- `keepFirst()` and `keepSecond()` are `<*` and `*>` respectively. Both parsers need to succeed but only the result from one of them is returned.

### Laws

#### Identity

```
pure(identity())->apply($parser) ≡ $parser
```

#### Homomorphism

```
pure($f)->apply(pure($x)) ≡ pure($f($x))
```

#### Interchange

```
$p->apply(pure($x)) 
    ≡ pure(fn($f) => $f($x))->apply($p)
```

#### Composition

```
// Assuming that
$compose = fn($f, $g) => fn($x) => $f($g($x))  

pure($compose)->apply($p1)->apply($p2)->apply($p3) 
    ≡ $p1->apply($p2->apply($p3))
``` 

#### Map

```
pure($f)->apply($parser) ≡ $parser->map($f)
```

## Monads

`Parser<T>` is a monad. 

- `pure()`: see above.
- `sequence()` runs two parsers in sequence, dropping the result of the first one. Both parsers consume input. You may know this as `>>`. The type of sequence is `Parser<T> -> Parser<T2> -> Parser<T2>`.
- `bind()` sequentially composes a parser and a parser-constructing function, passing the output produced by the first parser as an argument to the second.  Both parsers consume input. You may know this as `>>=` or `flatmap`. Type: `Parser<T> -> (T -> Parser<T2>) -> Parser<T2>`.


### Laws

Left identity: 

```
bind(pure($a), $f) 
    ≡ pure($a)->bind($f) 
    ≡ $f($a)
``` 

Right identity: 

```
bind($parser, 'pure') 
    ≡ $parser->bind('pure') 
    ≡ $parser
```

Associativity:

```
$parser->bind($f)->bind($g) 
    ≡ $parser->bind(fn($x) (use $f, $g) => $f($x)->bind($g))
```
