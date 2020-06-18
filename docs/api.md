# API

## Characters

### char($c) : Parser

Parse a single character.

### charI($c) : Parser

Parse a single character, case-insensitive and case-preserving. On success it returns the string cased as the
actually parsed input.
eg charI('a'')->run("ABC") will succeed with "A", not "a".

### string($str) : Parser

Parse a non-empty string.

### stringI($str) : Parser

Parse a non-empty string, case-insensitive and case-preserving. On success it returns the string cased as the
actually parsed input.
eg stringI("foobar")->run("foObAr") will succeed with "foObAr"

### controlChar() : Parser

Parse a control character (a non-printing character of the Latin-1 subset of Unicode).

### upperChar() : Parser

Parse an uppercase character A-Z.

### lowerChar() : Parser

Parse a lowercase character a-z.

### alphaChar() : Parser

Parse an uppercase or lowercase character A-Z, a-z.

### alphaNumChar() : Parser

Parse an alpha or numeric character A-Z, a-z, 0-9.

### printChar() : Parser

Parse a printable ASCII char.

### punctuationChar() : Parser

Parse a single punctuation character !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~



## Combinators

### identity(Parser $parser) : Parser

Identity parser, returns the Parser as is.

### pure($output) : Parser

A parser that will have the argument as its output, no matter what the input was. It doesn't consume any input.

### optional(Parser $parser) : Parser

Optionally parse something, but still succeed if the thing is not there

### bind(Parser $parser, $f) : Parser

Create a parser that takes the output from the first parser (if successful) and feeds it to the callable. The callable
must return another parser. If the first parser fails, the first parser is returned.
This is a monadic bind aka flatmap.

### sequence(Parser $first, Parser $second) : Parser

Parse something, then follow by something else. Ignore the result of the first parser and return the result of the
second parser.

### keepFirst(Parser $first, Parser $second) : Parser

Sequence two parsers, and return the output of the first one.

### keepSecond(Parser $first, Parser $second) : Parser

Sequence two parsers, and return the output of the second one.

### either(Parser $first, Parser $second) : Parser

Either parse the first thing or the second thing

### assemble(Parser $parsers) : Parser

Append all the passed parsers.

### collect(Parser $parsers) : Parser

Parse into an array that consists of the results of all parsers.

### any(Parser $parsers) : Parser

Tries each parser one by one, returning the result of the first one that succeeds.

### atLeastOne(Parser $parser) : Parser

One or more repetitions of Parser

### repeat($n, Parser $parser) : Parser

Parse something exactly n times

### many(Parser $parser) : Parser

Parse something zero or more times, and output an array of the successful outputs.

### some(Parser $parser) : Parser

Parse something one or more times, and output an array of the successful outputs.

### between(Parser $open, Parser $middle, Parser $close) : Parser

Parse $open, followed by $middle, followed by $close, and return the result of $middle. Useful for eg. "(value)".



## Numeric

### digitChar() : Parser

Parse 0-9. Returns the digit as a string. Use ->map('intval')
or similar to cast it to a numeric type.

### float() : Parser

Parse a float. Returns the float as a string. Use ->map('floatval')
or similar to cast it to a numeric type.

### binDigitChar() : Parser

Parse a binary character 0 or 1.

### octDigitChar() : Parser

Parse an octodecimal character 0-7.

### hexDigitChar() : Parser

Parse a hexadecimal numeric character 0123456789abcdefABCDEF.



## Primitives

### satisfy($predicate, $expected) : Parser

A parser that satisfies a predicate. Useful as a building block for writing things like char(), digit()...

### skipWhile($predicate) : Parser

Skip 0 or more characters as long as the predicate holds.

### skipWhile1($predicate) : Parser

Skip 1 or more characters as long as the predicate holds.

### takeWhile($predicate) : Parser

Keep parsing 0 or more characters as long as the predicate holds.

### takeWhile1($predicate) : Parser

Keep parsing 1 or more characters as long as the predicate holds.

### anySingle() : Parser

Parse and return a single character of anything.

### anything() : Parser

Parse and return a single character of anything.

### anySingleBut($x) : Parser

Match any character but the given one.

### oneOf($chars) : Parser

Succeeds if the current character is in the supplied list of characters. Returns the parsed character.

### oneOfS($chars) : Parser

A compact form of 'oneOf'.
oneOfS("abc") == oneOf(['a', 'b', 'c'])

### noneOf($chars) : Parser

The dual of 'oneOf'. Succeeds if the current character is not in the supplied list of characters. Returns the
parsed character.

### noneOfS($chars) : Parser

A compact form of 'noneOf'.
noneOfS("abc") == noneOf(['a', 'b', 'c'])

### takeRest() : Parser

Consume the rest of the input and return it as a string. This parser never fails, but may return the empty string.

### nothing() : Parser

Parse nothing, but still succeed.
This serves as the zero parser in `append()` operations.

### everything() : Parser

Parse everything; that is, consume the rest of the input until the end.

### success() : Parser

Always succeed, no matter what the input was.

### failure() : Parser

Always fail, no matter what the input was.

### eof() : Parser

Parse the end of the input



## Recursion

### recursive() : Parser

Create a recursive parser. Used in combination with recurse(Parser).
For an example see {@see RecursiveParserTest}.



## Space

### space() : Parser

Parse a single space character.

### tab() : Parser

Parse a single tab character.

### blank() : Parser

 Parse a space or tab.

### whitespace() : Parser

 Parse a space character, and \t, \n, \r, \f, \v.

### newline() : Parser

Parse a newline character.

### crlf() : Parser

Parse a carriage return character and a newline character. Return the two characters. {\r\n}

### eol() : Parser

Parse a newline or a crlf.

### skipSpace() : Parser

Skip zero or more white space characters.

### skipHSpace() : Parser

Like 'skipSpace', but does not accept newlines and carriage returns.

### skipSpace1() : Parser

Skip one or more white space characters.

### skipHSpace1() : Parser

Like 'skipSpace1', but does not accept newlines and carriage returns.



