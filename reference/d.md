D
===

 "D" is debug function for developers.
 "D" is very easy to readable in a table layout.
 In very common cases, developer will forget to turn off the debugging output.
 However, the "D" function is display only to developer.
 That feature has been realized "Env::isAdmin()" method.
 Yes, You can live codeing on production server.

## Usage

```php
//  This code can write production server. Not to see users.
D($_SESSION);
```
