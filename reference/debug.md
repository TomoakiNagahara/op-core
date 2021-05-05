Debugging is so easy!
===

## Usage

### Debug object

```php
//  Set debug information.
Debug::Set('id', $id);

//  Display debug infromation.
Debug::Out();

//  Debug of Unit
$app->Unit('SQL')->Debug();
```

```php
class {
  //  Use trait
  use OP_CORE, OP_DEBUG;
  
  //  Construct
  function __construct(){
    self::__DebugSet(__FUNCTION__, true);
  }
  
  //  Destruct
  function __destruct(){
    self::__DebugOut();
  }
}
```

### Debug trait

 Debug feature is included from OP_DEBUG trait.

```php
class Test {
  use OP_DEBUG;
}
```

 Set value.

```php
function Temp(){
  __DebugSet('key', $value);
}
```

 Display values.

 1. You can omit the key.
 1. Values ​​are stacked.

```php
Test::Debug('key');
```
