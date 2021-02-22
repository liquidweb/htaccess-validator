# Htaccess Validator

[Apache Htaccess files](http://httpd.apache.org/docs/current/howto/htaccess.html) can be a double-edged sword: on one hand, site owners can easily add new rewrites, configure headers, and more. On the other hand, one mistake can mean the whole site goes down.

This script aims to solve that problem, enabling changes to validated programmatically. Making changes to a site's `.htaccess` file? Validate your changes before applying them!

> ⚠️ **Using PHP?**<br>[Check out this script's companion Composer package](https://github.com/liquidweb/htaccess-validator-php).


## Requirements

As the package uses Apache2 itself to validate, it must be available within your environment. [The Liquid Web Knowledge Base has instructions for installing Apache on most popular platforms](https://www.liquidweb.com/kb/install-apache-2-ubuntu-18-04/).


## Usage

The `bin/validate-htaccess` script accepts a configuration file for validation:

```sh
$ bin/validate-htaccess /path/to/some/file.conf
```

The script will return a non-zero exit code if validation errors were detected. [Individual codes are documented in the script's header](bin/validate-htaccess#L15).
