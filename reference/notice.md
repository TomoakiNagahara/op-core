Notice is notice for developer
===

 All errors are caught. And will developer notice.
 Errors are displayed on the HTML. Or notice by EMail.

 That process is as follows.

 1. If an error occurs, first refer to the visitor's IP address.
 1. If the IP address is a developer, an error will be displayed in HTML on the screen.
 1. If the IP address does not match, onepiece-framework will send the error contents to the developer.

## Usage

### Config

 Add IP-address and email address of developer.

```php
Env::Admin('admin@example.com','192.168.1.2');
```

### Notice::Set()

 Add a notice.

### Notice::Get()

 Get the most recently added notice.

### Notice::Pop()

 Retrieves the last added notice.

### Notice::Has()

 Return boolean value, Whether there is notice.
