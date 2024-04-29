# Parsica


[![Tests](https://github.com/parsica-php/parsica/actions/workflows/tests.yml/badge.svg)](https://github.com/parsica-php/parsica/actions/workflows/tests.yml)

The easiest way to build robust parsers in PHP.

```bash
composer require parsica-php/parsica
```

Documentation & API: [parsica-php.github.io](https://parsica-php.github.io/)


```php
<?php
$parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
$result = $parser->tryString("{Hello}");
echo $result->output(); // Hello
```

![Twitter Follow](https://img.shields.io/twitter/follow/parsica_php?style=social)

## Project status

Regrettably, the maintainer of this library (@turanct) has passed away in December 2021 due to cancer. At the moment, there is no maintainer. If you'd like to contribute to this library, or if you wish to use this library for a project and need consulting, contact the original author Mathias Verraes mathias at verraes net.

## Development

After running `composer install`, run these to validate if everything is in working order:

```
composer run phpunit
composer run psalm
composer run uptodocs

# or all of them:

composer run test
```


