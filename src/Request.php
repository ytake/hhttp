<?hh

namespace Ytake\Hhttp;

use type Psr\Http\Message\UriInterface;
use type Psr\Http\Message\RequestInterface;
use namespace HH\Lib\{Str};

class Request implements RequestInterface {
  use RequestTrait;

  public function __construct(
    mixed $uri = null,
    HttpMethod $method = HttpMethod::GET,
    mixed $body = 'php://temp',
    Map<string, varray<string>> $headers = Map{}
  ) {
    $this->initialize($uri, $method, $body, $headers);
  }

  <<__Override>>
  public function getHeaders() {
    $headers = $this->headers;
    $uri = $this->uri;
    invariant($uri is UriInterface, 'uri error.');
    if (!$this->hasHeader('host') && $uri->getHost() is string) {
      $headers->add(Pair{'Host', [$this->getHostFromUri()]});
    }
    return $headers;
  }

  <<__Override>>
  public function getHeader($header) {
    $uri = $this->uri;
    invariant($uri is UriInterface, 'uri error.');
    if (! $this->hasHeader($header)) {
      if (Str\lowercase($header) === 'host' && $uri->getHost()) {
        return [$this->getHostFromUri()];
      }
      return [];
    }
    $header = $this->headerNames[Str\lowercase($header)];
    return $this->headers->at($header);
  }
}
