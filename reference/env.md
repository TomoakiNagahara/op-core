The onepiece-framework's environment variable is stored on the Env.
===

## Usage

### General

```php
Env::Set('test', true);
$test = Env::Get('test');
```

### Admin

```php
//  Register admin IP Address.
Env::Set(Env::_ADMIN_IP_, '192.168.1.2');

//  Register admin EMail address.
Env::Set(Env::_ADMIN_MAIL_, 'info@example.com');

//  Checking if admin. Always true for localhost.
$io = Env::isAdmin();

//  Checking if localhost.
$io = Env::isLocalhost();
```

### Mime method is automatically adjust MIME type.

```php
Env::Mime('text/plain');
```
