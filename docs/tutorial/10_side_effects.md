---
title: Side Effects and Events
---

Sometimes you may want to perform actions when your parser encounters something you're interested in. Parsica provides combinator called `emit()`. It allows you to inject side effects at any point. It's intentionally very barebones: It's really just a callback function, that gets called only when the parser succeeds.


```php
<?php
// Define a function that takes the output and performs some side effect:
$print = fn(string $output) => print($output);
// Define a parser:
$parser = many(either(
    char('a'),
    // Combine the 'b' parser with emit:
    char('b')->emit($print)
));
// Running the parser calls print() whenever a 'b' is encountered:
$parser->tryString('aababba'); // Prints "bbb"
```

Using closures and mutable objects, you can embed mutability into a parsing process.

```php
<?php
final class Counter
{
    private int $count = 0;
    function incr(): void { $this->count++; }
    function count(): int{ return $this->count; }
}

// Make a mutable object:
$counter = new Counter();
// Use it inside a closure:
$incr = fn(string $output) => $counter->incr();
$parser = many(either(
    char('a'),
    // Increment counter when we hit 'b'
    char('b')->emit($incr)
));
$parser->tryString('aababba');
assertSame(3, $counter->count());
```


For most use cases, we suggest using `emit()` with an adapter for your application's event dispatching mechanism.  The following shows how to adapt `emit()` to any [PSR-14](https://www.php-fig.org/psr/psr-14/) compatible event dispatcher. 


```php
<?php
// Your (or your framework's) event dispatcher:
final class YourDispatcher implements \Psr\EventDispatcher\EventDispatcherInterface
{
    public function dispatch(object $event) { /* ... */ }
}
$yourDispatcher = new YourDispatcher();

// An adapter that turns a value into an event and sends it to your dispatcher:
$yourAdapter = function (Colour $colour) use ($yourDispatcher) : void {
    $timestamp = new DateTimeImmutable("now");
    $event = new ColourWasEncountered($timestamp, $colour);
    $yourDispatcher->dispatch($event);
};
$parser = many(
    either(
        string('red'),
        string('green'),
        string('blue'),
    )
        // The parser outputs string, the map() combinator turns those into domain objects:
        ->map(fn(string $output) : Colour => new Colour($output))
        // Emit the Colour object to the adapter:
        ->emit($yourAdapter)
);
```

This way, you can neatly separate the occurrence of a parsing event, from the actual side effect. If the dispatcher is asynchronous, the parsing process can keep continuing, without being interrupted by blocking side effects, such as writing to a database. Or when parsing a large input file or continuous input stream, you can start processing the results before the parsing has finished.
