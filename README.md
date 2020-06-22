# Parsica
 
The easiest way to build robust parsers in PHP.

[https://parsica.verraes.net/](https://parsica.verraes.net/)


```php
<?php
$parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
$result = $parser->try("{Hello}");
echo $result->output(); // Hello
```
