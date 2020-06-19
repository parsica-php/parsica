# Parser Combinator

The easiest way to build robust parsers.

[Documentation](docs/index.md)

```php
<?php
$parser = char(':')
            ->append(atLeastOne(punctuationChar()))
            ->label('smiley');
$result = $parser->try(':*{)'); 
echo $result->output() . " is a valid smiley!";
```
