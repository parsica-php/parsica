---
title: Dealing with Space
---

Parsica comes with a number of useful parsers for dealing with different types of whitespace and newlines, as well with required or optional whitespace. We recommend browsing `src/space.php` to see what is available, so you don't need to build your own parsers for that. 

## Space consumers

When building a parser for say a language or a file format, you often have specific rules about space. Whitespace can be required or optional, and expressions can be valid or invalid if they contain newlines. All of these are could be valid or invalid depending on your case:

```
// with 0, 1, or more spaces
1+1
1 + 1
1   +   1

// multiline
1 +
2

// tabs
1 
  + 2
```

There's too much variation for Parsica to provide a single solution. However, you don't want to litter your code with space parsers everywhere:

```php
<?php
$term = digitChar();
$operator = char('+');
$parser = collect(
        $term,
        skipSpace1(),
        $operator,
        skipSpace1(),
        $term,
        skipSpace1(),
    )->map(fn($o) => $o[0] + $o[4]);

$result = $parser->tryString("1  +\n  2\t");
assertSame(3, $result->output());
```

This is noisy. And if you want to change the rules about whitespace or build more complex parsers, you have to deal with this problem all the time, making it unmaintainable (or at least annoying).

The idea is to build a space consumer that you can reuse everywhere. The space consumer is a parser combinator that you wrap around another parser, and that returns the output of the inner parser, ignoring whitespace. A typical approach is to consistently ignore space after the thing you're interested in. 

```php
<?php
// $token behaves just like $parser, but requires the parsed 
// value to be followed by at least 1 space
$token = fn(Parser $parser) => keepFirst($parser, skipSpace1());

// Now we wrap our parsers
$term = $token(digitChar());
$operator = $token(char('+'));

// Our main parser now has the same "shape" as the expression we're trying to parse:
$parser = collect(
        $term,
        $operator,
        $term,
    )->map(fn($o) => $o[0] + $o[2]);

$result = $parser->tryString("1  +\n  2\t");
assertSame(3, $result->output());
```

Now, all the logic for skipping space is nicely contained in `$token`. If we wanted to disallow multiline expressions, we only need to replace `skipSpace1()` with `skipHSpace1()` in one place.

As an example, here's an excerpt from the JSON parser, using the ws (whitespace) as defined in the JSON spec:

```php
final class MyJSON
{ 
    public static function ws(): Parser
    {
        return zeroOrMore(satisfy(isCharCode([0x20, 0x0A, 0x0D, 0x09])))->voidLeft(null)
            ->label('whitespace');
    }

    public static function token(Parser $parser): Parser
    {
        return keepFirst($parser, JSON::ws());
    }

    public static function object(): Parser
    {
        return map(
            between(
                JSON::token(char('{')),
                JSON::token(char('}')),
                sepBy(
                    JSON::token(char(',')),
                    JSON::member()
                )
            ),
            fn(array $members):object => (object)array_merge(...$members));
    }

    // see src/JSON/JSON.php for the full code
}
```

If you have multiple ways of handling space in one parser, you can of course define multiple space consumers and give them relevant names.

