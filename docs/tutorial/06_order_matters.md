---
title: Order matters
sidebar_label: Order matters
---


The order of clauses in an or() matters. If we do the following parser definition, the parser will consume "http", even if the strings starts with "https", leaving "s://..." as the remainder.

```php
<?php
$parser = string('http')->or(string('https'));
$input = "https://parsica.verraes.net";
$result = $parser->tryString($input);
assertEquals("http", $result->output());
assertEquals("s://parsica.verraes.net", $result->remainder());
```

The solution is to consider the order of or clauses:

```php
<?php
$parser = string('https')->or(string('http'));
$input = "https://parsica.verraes.net";
$result = $parser->tryString($input);
assertEquals("https", $result->output());
assertEquals("://parsica.verraes.net", $result->remainder());
```
