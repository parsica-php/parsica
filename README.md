# Parsica
 
The easiest way to build robust parsers in PHP.

[https://parsica.verraes.net/](https://parsica.verraes.net/)


```php
<?php
$parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
$result = $parser->tryString("{Hello}");
echo $result->output(); // Hello
```

## Badges

![Twitter Follow](https://img.shields.io/twitter/follow/parsica_php?style=social)
![Test status](https://img.shields.io/github/workflow/status/mathiasverraes/parsica/Test?label=tests)
![License](https://img.shields.io/github/license/mathiasverraes/parsica)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/mathiasverraes/parsica)


