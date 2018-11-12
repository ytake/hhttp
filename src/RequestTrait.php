<?hh

namespace Ytake\Hhttp;

use type Psr\Http\Message\UriInterface;
use type Psr\Http\Message\RequestInterface;
use namespace HH\Lib\{Str, C};

use function preg_match;

trait RequestTrait {

  use MessageTrait;
  require implements RequestInterface;

  private ?string $method;
  private ?string $requestTarget;
  private ?UriInterface $uri;
  
  private  function initialize(
    mixed $uri = null,
    ?string $method = null,
    mixed $body = 'php://memory',
    Map<string, varray<string>> $headers = Map{}
  ) : void {
    if ($method !== null) {
      $this->setMethod($method);
    }
    $uri    = $this->createUri($uri);
    $this->stream = $this->getStream($body, 'wb+');
    $this->setHeaders($headers);

    if (! $this->hasHeader('Host') && $uri->getHost()) {
      $this->headerNames->add(Pair{'host', 'Host'});
      $this->headers->add(Pair{'Host', [$this->getHostFromUri()]});
    }
    $this->uri = $uri;
  }

  private function createUri(mixed $uri) : UriInterface {
    if ($uri is UriInterface) {
      return $uri;
    }
    if ($uri is string) {
      return new Uri($uri);
    }
    if ($uri === null) {
      return new Uri();
    }
    throw new Exception\InvalidArgumentException(
      'Invalid URI provided; must be null, a string, or a Psr\Http\Message\UriInterface instance'
    );
  }

  private function getHostFromUri() : string {
    $uri = $this->uri;
    $host = '';
    if ($uri is UriInterface) {
      $host  = $uri->getHost();
      $host .= $uri->getPort() ? ':' . $uri->getPort() : '';
    }
    return $host;
  }

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

  public function withRequestTarget($requestTarget): this {
    if (preg_match('#\s#', $requestTarget)) {
      throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
    }
    $new = clone $this;
    $new->requestTarget = $requestTarget;
    return $new;
  }
  
  <<__Rx>>
  public function getMethod(): string {
    if ($this->method is string) {
      return $this->method;
    }
    return '';
  }

  public function withMethod($method): this {
    if (!$method is string) {
      throw new \InvalidArgumentException('Method must be a string');
    }
    $new = clone $this;
    $new->method = $method;
    return $new;
  }

  private function setMethod(string $method): void {
    if (! preg_match('/^[!#$%&\'*+.^_`\|~0-9a-z-]+$/i', $method)) {
      throw new Exception\InvalidArgumentException(Str\format(
        'Unsupported HTTP method "%s" provided',
        $method
      ));
    }
    $this->method = $method;
  }

  <<__Rx>>
  public function getUri(): UriInterface {
    invariant($this->uri is UriInterface, "type error.");
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
    $uri = $this->uri;
    invariant($uri is UriInterface, "type error.");
    if ('' === $host = $uri->getHost()) {
      return;
    }
    if (null !== ($port = $uri->getPort())) {
      $host .= ':'.$port;
    }
    if (!$this->headerNames->contains('host')) {
      $this->headerNames->add(Pair{'host', 'Host'});
    }
    $this->headers = $this->headers->add(
      Pair{
        $this->headerNames->at('host'), 
        [$host]
      }
    );
  }
}
