# Performance


## XDebug

Turn off XDebug, as it will make things much slower. If you do need it, you may need to increase the nesting level, either in code or in `php.ini`:

```php
<?php
ini_set('xdebug.max_nesting_level', 5000);
```

```ini
xdebug.max_nesting_level=5000
```

