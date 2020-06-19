---
id: tutorial/order_matters
title: Order matters
sidebar_label: Order matters
---


The order of clauses in an or() matters. If we do the following parser definition, the parser will consume "http", even if the strings starts with "https", leaving "s://..." as the remainder.

```php
<?php
$parser = string('http')->or(string('https'));
$input = "https://verraes.net";
$result = $parser->run($input);
assert($result->output() === "http");
assert($result->remainder() === "s://verraes.net");
```

The solution is to consider the order of or clauses:

```php
<?php
$parser = string('https')->or(string('http'));
$input = "https://verraes.net";
$result = $parser->run($input);
assert($result->output() === "https");
assert($result->remainder() === "://verraes.net");
```
