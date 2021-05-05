onepiece-framework's standard functions
===

## Meta path

### RootPath()

 `RootPath()` is register function of meta path.

```php
\OP\RootPath('test', __DIR__);
```

### ConvertPath()

 `ConvertPath()` is convert meta path to real path.
 That is registered by RootPath.

```php
\OP\ConvertPath('test:/');
```

### ConvertURL()

 `ConvertURL()` is convert meta path to URL.
 However domain name is not included.

```php
\OP\ConvertURL('test:/');
```

### Html()

```php
\OP\Html('Hello, world.', 'DIV #id .class_1 .class_2');
```
