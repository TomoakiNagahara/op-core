Apache's settings
===

## For admin settings

 `ADMIN_IP` sets the IP address that return `true` in `Env::isAdmin()`.

```
SetEnv  ADMIN_IP    192.168.0.1
```

 If an error occurs, The onepiece-framework will send the information to the following email address.

```
SetEnv  ADMIN_MAIL  root@localhost
```

## Reversible encryption settings

```
SetEnv  _APP_OPENSSL_IV_        1234567890abcdef
SetEnv  _APP_OPENSSL_PASSWORD_  1234567890abcdef
```

## Setting not to save logs - Prevents log bloat

 Image logs are not save.

```
SetEnvIf Request_URI "\.(gif)|(jpg)|(png)|(css)|(js)|(ico)$" nolog
```

 Does not save admin-ip logs.

```
SetEnvIf Remote_Addr ^192\.168\.0\.1 nolog admin
```

 Does not save crawler logs.

```
SetEnvIf User-Agent "crawler"   nolog
SetEnvIf User-Agent "spider"    nolog
SetEnvIf User-Agent "msnbot"    nolog
SetEnvIf User-Agent "bingbot"   nolog
SetEnvIf User-Agent "Googlebot" nolog
```
