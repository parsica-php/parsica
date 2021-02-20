---
title: Installation & Requirements
---


import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

<Tabs
  defaultValue="cli"
  values={[
    { label: 'Command line', value: 'cli', },
    { label: 'composer.json', value: 'composer', },
  ]
}>
<TabItem value="cli">

```bash
composer require parsica-php/parsica
```

</TabItem>
<TabItem value="composer">

```json
"require": {
    "parsica-php/parsica": "dev-main"
}
```

</TabItem>

</Tabs>


## Requirements

- PHP 7.4 or higher
- [The multibyte string extension for PHP (aka mbstring)](https://www.php.net/manual/en/book.mbstring.php)

(@TODO: add polyfill for mbstring).


## Usage

In a .php file, make sure the Composer autoloader is included:

`require_once __DIR__.'/../vendor/autoload.php';`

Import parsers and combinators:

`use function Parsica\Parsica\char;`

You can combine multiple imports in one statement: 

`use function Parsica\Parsica\{between, char, atLeastOne, alphaChar};`

Finally, add some code:

```php
<?php
$parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
$result = $parser->tryString("{Hello}");
echo $result->output();
// outputs "Hello"
```

