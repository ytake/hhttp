<?hh

namespace Ytake\Hhttp;

use type Facebook\Experimental\Http\Message\UriInterface;
use type Facebook\Experimental\Http\Message\RequestInterface;

class Request implements RequestInterface {
  use RequestTrait;

  public function __construct(
    mixed $uri = null,
    HttpMethod $method = HttpMethod::GET,
    mixed $body = 'php://temp',
    Map<string, varray<string>> $headers = Map{},
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
    if ('' !== $body && null !== $body) {
      $this->stream = $this->getStream($body);
    }
  }
}
