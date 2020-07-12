---
title: Performance
sidebar_label: Performance
---


At the time of writing, no effort has been made to measure the performance of Parsica. That doesn't mean it's slow; it means that we don't know yet. If you're going to use this on large amounts of data, do some profiling yourself first. Compute == carbon, and we'd like to keep this planet a little longer. You can help by contributing your profiling and optimisations. 
 
We have some ideas that will allow us to make it very efficient, and we intend to do that before getting to a 1.0 release.


## XDebug

Turn off XDebug, as it will make things much slower. If you do turn on XDebug, you may get `Maximum function nesting level of '256' reached, aborting!`. Increase the nesting level until the error goes away, either in code or in `php.ini`:

```php
<?php
ini_set('xdebug.max_nesting_level', '1024');
```

```ini
xdebug.max_nesting_level=1024
```

## Recursion

If you encounter a "Maximum function nesting level" error, the more likely problem is that you're building a recursive parser incorrectly. Have a look at the documentation page about recursion to learn more.
