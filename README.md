# Hungrr / HTTP Message implementation

[![Build Status](https://travis-ci.org/ytake/hhttp.svg?branch=master)](https://travis-ci.org/ytake/hhttp)

`ytake/hungrr` is a Hack package containing implementations of the
[PSR-7 HTTP message interfaces](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md)

and [PSR-17 HTTP message factory interfaces](https://www.php-fig.org/psr/psr-17).

Not Supported PHP

## Usage

### Marshaling an incoming request

```hack
<?hh // strict

use type Ytake\Hungrr\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals();
```
