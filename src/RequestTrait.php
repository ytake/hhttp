<?hh

namespace Ytake\Hhttp;

use type Psr\Http\Message\UriInterface;
use type Psr\Http\Message\RequestInterface;

use function preg_match;

trait RequestTrait {

  use MessageTrait;
  require implements RequestInterface;

  private string $method;
  private ?string $requestTarget;
  private UriInterface $uri;
  
  public function getRequestTarget(): string {
    if ($this->requestTarget is nonnull) {
      return $this->requestTarget;
    }
    $target = '';
    $uri = $this->uri;
    if ($uri is UriInterface) {
      if ('' === $target = $uri->getPath()) {
        $target = '/';
      }
      if ('' !== $uri->getQuery()) {
        $target .= '?'.$uri->getQuery();
      }
    }
    return $target;
  }

  public function withRequestTarget(string $requestTarget): this {
    if (preg_match('#\s#', $requestTarget)) {
      throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
    }
    $new = clone $this;
    $new->requestTarget = $requestTarget;
    return $new;
  }
  
  <<__Rx>>
  public function getMethod(): string {
    return $this->method;
  }

  public function withMethod($method): this {
    if (!$method is string) {
      throw new \InvalidArgumentException('Method must be a string');
    }
    $new = clone $this;
    $new->method = $method;
    return $new;
  }

  <<__Rx>>
  public function getUri(): UriInterface {
    return $this->uri;
  }

  public function withUri(UriInterface $uri, $preserveHost = false): this {
    if ($uri === $this->uri) {
      return $this;
    }
    $new = clone $this;
    $new->uri = $uri;
    if (!$preserveHost || !$this->hasHeader('Host')) {
      $new->updateHostFromUri();
    }
    return $new;
  }

  private function updateHostFromUri(): void {
    if ('' === $host = $this->uri->getHost()) {
      return;
    }
    if (null !== ($port = $this->uri->getPort())) {
      $host .= ':'.$port;
    }
    if ($this->headerNames->contains('host')) {
      $header = $this->headerNames->at('host');
    } else {
      $header = 'Host';
      $this->headerNames->add(Pair{'host', 'Host'});
    }
    $this->headers = $this->headers->add(Pair{$header, [$host]});
  }
}
