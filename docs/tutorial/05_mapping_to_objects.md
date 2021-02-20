---
title: Mapping to Objects
---

## Parser types

Most of the parsers that come with Parsica, return strings as outputs.

```php
<?php
$parser = digitChar();
assertInstanceOf('Parsica\Parsica\Parser', $parser);

$result = $parser->tryString('1');
assertIsString('Parsica\Parsica\StringStream', $result->output());
assertEquals('1', $result->output());
```

In PHP 7.x, the type of `$parser` is `Parser`, but you can think of it having the type `Parser<string>`. PHP doesn't support generics, so it doesn't enforce that. However, working with Parsica is easier if you always think of parsers having an inner type. 

> `Parser<T>` means that if we successfully run the parser on an input, it will output a value of type `T`.  

Here's an example of a parser of type `Parser<array<string>>`:

```php
<?php
$parser = sepBy(char(','), atLeastOne(digitChar()));
$result = $parser->tryString('123,9,55');
assertEquals(["123", "9", "55"], $result->output());
```

## The map combinator

The point of parsing to turn strings into more useful data structures. The combinator `map` can help you with that. It does the same thing as PHP's `array_map` function. You combine a parser and a `callable`, and you get a new parser. This new parser will apply the callable to the output of the parser.

We can use it for manipulating the output. Here's a simple example:

```php
<?php
$parser = atLeastOne(alphaChar())
    ->map(fn(string $val) => strtolower($val));
$result = $parser->tryString('PaRsIcA');
assertEquals("parsica", $result->output());
```

If the parser fails, the callable is not applied to the output (because there is no output). So you don't need to worry about error handling.

## Casting to scalars

We can now use this to cast the parser's output to scalars:

```php
<?php
$parser = atLeastOne(digitChar())
    ->map(fn(string $val) => intval($val));
$result = $parser->tryString("123"); // input is still a string
assertSame(123, $result->output()); // output is an int
```

It also works inside nested parsers. We can use this on the `sepBy` example from above:

```php
<?php
$parser = sepBy(
    char(','), 
    atLeastOne(digitChar())
        ->map(fn($val) => intval($val))
);
$result = $parser->tryString('123,9,55');
assertSame([123, 9, 55], $result->output()); // array of ints
```

The type of this last parser is now `Parser<array<int>>` instead of the original `Parser<array<string>>`. 

## Casting to objects

We'll want to cast to much more interesting data structures than scalars and arrays. Let's parse some monetary values into a nested value object structure. `Money` is composed of an integer value and a `Currency` value object:

```php


final class Currency
{
    private string $currency;

    function __construct(string $currency)
    {
        $this->currency = $currency;
    }
}

// Side warning: don't actually use floats to do computations with money.
final class Money
{
    private float $amount;
    private Currency $currency;

    function __construct(float $amount, Currency $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }
}

// $currency is a parser of type Parser<Currency>
$currency = repeat(3, upperChar())
    ->map(fn(string $c) => new Currency($c));

// $amount has type Parser<float>
$amount = float()
    ->map(fn(string $val) => floatval($val));

// $money has type Parser<[Currency, float]) because collect() has type Parser<[T]>
$money = collect($currency, skipHSpace()->followedBy($amount));

// Let's change $money to type Parser<Money>
$money = $money->map(fn(array $a) => new Money($a[1], $a[0]));

$result = $money->tryString('EUR 12.34');
assertEquals(new Money(12.34, new Currency('EUR')), $result->output());

// We can now composer our Parser<Money> in larger parsers
// $pricelist has type Parser<array<Money>>
$priceList = collect(
    string("exVAT ")->followedBy($money)->thenIgnore(whitespace()),
    string("incVAT ")->followedBy($money)
);
$result = $priceList->tryString('exVAT EUR 100.00 incVAT EUR 121.00');

```


