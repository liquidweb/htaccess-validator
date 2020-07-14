# Htaccess Validator

[Apache Htaccess files](http://httpd.apache.org/docs/current/howto/htaccess.html) can be a double-edged sword: on one hand, site owners can easily add new rewrites, configure headers, and more. On the other hand, one mistake can mean the whole site goes down.

This Composer package aims to solve that problem, enabling changes to validated programmatically. Making changes to a site's `.htaccess` file? Validate your changes before applying them!


## Installation

The easiest way to install the package is via [Composer](https://getcomposer.org):

```
$ composer require liquidweb/htaccess-validator
```

As the package uses Apache2 itself to validate, it must be available within your environment. [The Liquid Web Knowledge Base has instructions for installing Apache on most popular platforms](https://www.liquidweb.com/kb/install-apache-2-ubuntu-18-04/).


## Usage

There are two main ways to use the validator:

1. As a stand-alone tool via the command line
2. As a PHP library (requires `proc_open` to be available)

### Validating Apache2 configurations from the command line interface (CLI)

The `bin/validate-htaccess` script accepts a configuration file for validation:

```sh
$ bin/validate-htaccess /path/to/some/file.conf
```

The script will return a non-zero exit code if validation errors were detected. Individual codes are documented in the script's header](bin/validate-htaccess#L15)

### Validating Apache2 configurations within a PHP script

The `LiquidWeb\HtaccessValidator\Validator` class serves as a wrapper around the `bin/validate-htaccess` script, enabling applications to validate Apache2 configurations programmatically.

There are two ways to instantiate the class:

1. Passing the full system path of the file under validation to the class constructor:

	```php
    use LiquidWeb\HtaccessValidator\Validator;

	$validator = new Validator($file);
	```

2. Passing the configuration directly to the `::createFromString()` factory method:

    ```php
    use LiquidWeb\HtaccessValidator\Validator;

	$validator = Validator::createFromString('Options +FollowSymLinks');
	```

Once you have a Validator instance, you may validate it in two ways:

```php
# Throws a LiquidWeb\HtaccessValidator\Exceptions\ValidationException upon failure.
$validator->validate();

# Return a boolean.
$validator->isValid();
```
