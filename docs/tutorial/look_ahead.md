---
title: Look Ahead
---

## notFollowedBy
     
Say you want to match the `print` keyword in a programming language. You can express that with the `string("print")` parser, but it will match more than you'd like:

```php
<?php
$print = string("print");

$result = $print->run("print('Hello World');");
assert($result->output() == "print");

$result = $print->run("printXYZ('Hello World');");
assert($result->output() == "print"); // oops!
```

As you can see, "printXYZ" also results in "print", but it wasn't our intention, because "printXYZ" is not a valid keyword.

We can solve it by using the `notFollowedBy` combinator.

```php
<?php
$print = keepFirst(string("print"), notFollowedBy(alphaNumChar()));
$result = $print->run("printXYZ('Hello World');");
assert($result->isFail());
```

There's a fluent interface as well:

```php
<?php
$print = string("print")->notFollowedBy(alphaNumChar());
$result = $print->run("printXYZ('Hello World');");
assert($result->isFail());
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

$result = $parser->try("print('Hello World');");
assert($result->output() == "print");

$result = $parser->try("printf('Hello %s', 'world');");
assert($result->output() == "printf");
```
