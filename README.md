# Parsica
 
The easiest way to build robust parsers in PHP.

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
