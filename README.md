# Parsica - PHP Parser Combinators
 
The easiest way to build robust parsers.

[Documentation](https://parsica.verraes.net/)

### Example

```php
<?php
$parser = char(':')
            ->append(atLeastOne(punctuationChar()))
            ->label('smiley');
$result = $parser->try(':*{)'); 
echo $result->output() . " is a valid smiley!";
```
