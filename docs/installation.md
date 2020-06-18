# Installation

## Requirements

- PHP 7.4 or higher
- [The multibyte string extension for PHP (aka mbstring)](https://www.php.net/manual/en/book.mbstring.php)
- [Composer](https://getcomposer.org/)

## Composer
 
`composer require mathiasverraes/parser-combinator`

Or add this to your `composer.json` file and run `composer update` 

```json
"require": {
    "mathiasverraes/uptodocs": "dev-main"
}
```

## Usage

In a .php file, make sure the Composer autoloader is included:

`require_once __DIR__.'/../vendor/autoload.php';`

Import parsers and combinators:

`use function Mathias\ParserCombinator\char;`

You can combine multiple imports in one statement: 
`
`use function Mathias\ParserCombinator\{between, char, atLeastOne, alphaChar};`

Finally, add some code:

```php
<?php
$parser = between(char('{'), atLeastOne(alphaChar()), char('}'));
$result = $parser->try("{Hello}");
echo $result->output();
// outputs "Hello"
```

