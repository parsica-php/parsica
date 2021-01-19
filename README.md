# Parsica
 
The easiest way to build robust parsers in PHP.

```bash
composer require mathiasverraes/parsica
```

Documentation & API: [parsica.verraes.net](https://parsica.verraes.net/)


```php
<?php
$parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
$result = $parser->tryString("{Hello}");
echo $result->output(); // Hello
```

![Twitter Follow](https://img.shields.io/twitter/follow/parsica_php?style=social)


## Development

After running `composer install`, run these to validate if everything is in working order:

```
composer run phpunit
composer run psalm
composer run uptodocs

# or all of them:

composer run test
```


