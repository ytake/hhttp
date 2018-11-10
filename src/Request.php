<?hh // strict

namespace Ytake\Hhttp;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface {
  use RequestTrait;

  public function __construct(
    mixed $uri = null, 
    ?string $method = null, 
    mixed $body = 'php://temp', 
    Map<string, varray<string>> $headers = Map{}
  ) {
    $this->initialize($uri, $method, $body, $headers);
  }
}