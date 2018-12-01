# Hungrr / HTTP Message implementation

[![Build Status](https://travis-ci.org/ytake/hungrr.svg?branch=master)](https://travis-ci.org/ytake/hungrr)

`ytake/hungrr` is a Hack package containing implementations of the
[Hack HTTP Request and Response Interfaces](https://github.com/hhvm/hack-http-request-response-interfaces)

PSR-7 was designed for PHP, not Hack, and some descisions do not fit smoothly with Hack's type system.

Not Supported PHP

## Requirements
HHVM 3.29 and above.

## Install

via Composer

```bash
$ hhvm $(which composer) ytake/hungrr
```

## Usage

## Marshaling an incoming request

```hack
<?hh // strict

use type Ytake\Hungrr\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals();
```

## Response

### Json Response

Constructor Detail

```text
public function __construct(
  ImmMap<mixed, mixed> $payload,
  Ytake\Hungrr\StatusCode $status,
  dict<string, vec<string>> $headers,
  int $encodingOptions
)
```

Example

```hack
<?hh // strict

use type Ytake\Hungrr\Uri;
use type Ytake\Hungrr\StatusCode;
use type Ytake\Hungrr\Response\RedirectResponse;

$r = new JsonResponse(new ImmMap([
  'json_encode' => ImmMap{
    'HHVM' => 'Hack'
  }
]));
```

### Redirect Response

Constructor Detail

```text
public function __construct(
  mixed $uri,
  Ytake\Hungrr\StatusCode $status,
  dict<string, vec<string>> $headers
)
```

$uri, MUST be a string or Facebook\Experimental\Http\Message\UriInterface instance.

Example

```hack
<?hh // strict

use type Ytake\Hungrr\Uri;
use type Ytake\Hungrr\StatusCode;
use type Ytake\Hungrr\Response\RedirectResponse;

// use uri string
$r = new RedirectResponse('/foo/bar');

// use uri instance
$r = new RedirectResponse(new Uri('https://example.com:10082/foo/bar'));
```
