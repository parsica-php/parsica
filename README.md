# Parsica
 
The easiest way to build robust parsers in PHP.

```bash
composer require mathiasverraes/parsica
```

Documentation: [parsica.verraes.net](https://parsica.verraes.net/)


```php
<?php
$parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
$result = $parser->try("{Hello}");
echo $result->output(); // Hello
```


![Twitter Follow](https://img.shields.io/twitter/follow/parsica_php?style=social)
![Test status](https://img.shields.io/github/workflow/status/mathiasverraes/parsica/Test?label=tests)


