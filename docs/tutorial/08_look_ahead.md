---
title: Looking ahead
---

## notFollowedBy
     
Say you want to match the `print` keyword in a programming language. You can express that with the `string("print")` parser, but it will match more than you'd like:

```php
<?php
$print = string("print");

$result = $print->tryString("print('Hello World');");
assertEquals("print", $result->output());

$result = $print->tryString("printXYZ('Hello World');");
assertEquals("print", $result->output()); // oops!
```

As you can see, "printXYZ" also results in "print", but it wasn't our intention, because "printXYZ" is not a valid keyword.

We can solve it by using the `notFollowedBy` combinator.

```php
<?php
$print = keepFirst(string("print"), notFollowedBy(alphaNumChar()));
$result = $print->run(new StringStream("printXYZ('Hello World');"));
assertTrue($result->isFail());
```

There's a fluent interface as well:

```php
<?php
$print = string("print")->notFollowedBy(alphaNumChar());
$result = $print->run(new StringStream("printXYZ('Hello World');"));
assertTrue($result->isFail());
```


In practice, we'll have a lot more keywords than just the one. A good habit is to first generalize this to all the keywords in our language. Then, using our new `$keyword` parser constructor, we can match the exact variations we like: 

```php
<?php
$keyword = fn(string $name) => keepFirst(string($name), notFollowedBy(alphaNumChar()));

$parser = choice(
    $keyword('printf'),
    $keyword('print'),
    $keyword('sprintf')
);

$result = $parser->tryString("print('Hello World');");
assertEquals("print", $result->output());

$result = $parser->tryString("printf('Hello %s', 'world');");
assertEquals("printf", $result->output());
```
