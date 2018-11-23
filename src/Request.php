<?hh // strict

namespace Ytake\Hungrr;

use type Facebook\Experimental\Http\Message\UriInterface;
use type Facebook\Experimental\Http\Message\RequestInterface;
use namespace Facebook\Experimental\Http\Message;

class Request implements RequestInterface {
  use RequestTrait;

  public function __construct(
    mixed $uri,
    Message\HTTPMethod $method = Message\HTTPMethod::GET,
    string $body = '',
    dict<string, vec<string>> $headers = dict[],
    string $version = '1.1'
  ) {
    if ($uri is string) {
      $uri = new Uri($uri);
    }
    $this->method = $method;
    invariant($uri is UriInterface, "\$uri, not implements UriInterface");
    $this->uri = $uri;
    $this->setHeaders($headers);
    $this->protocol = $version;

    if (!$this->hasHeader('Host')) {
      $this->updateHostFromUri();
    }
    $this->createIO();
    $this->setBody($body);
  }
}
