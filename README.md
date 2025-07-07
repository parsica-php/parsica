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

## Quality

The code is was entirely built with Test-Driven Development, and type-checked with [Psalm](https://github.com/vimeo/psalm). It is probably bug-free. It is suitable for complex parsing requirements, and could even be used to build a programming language.
However, it might not be performant enough if you use it at a high scale. 


## Project Maintenance & Support

Regrettably, the maintainer of this library (@turanct) has passed away in December 2021 due to cancer. The original author @mathiasverraes is now the maintainer again, and is doing occasional minor updates. If you'd like to contribute to this library, or if you wish to use this library for a project and need consulting, contact Mathias via mathias at verraes net. PR and issues submissions may not be monitored.

## Development

After running `composer install`, run these to validate if everything is in working order:

```
composer run phpunit
composer run psalm
composer run uptodocs

# or all of them:

composer run test
```


