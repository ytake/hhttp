<?hh // strict

namespace Ytake\Hhttp;

use type Facebook\Experimental\Http\Message\UriInterface;
use type Facebook\Experimental\Http\Message\RequestInterface;
use type Facebook\Experimental\Http\Message\RequestURIOptions;

use namespace HH\Lib\Experimental\IO;
use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\Regex;

trait RequestTrait {
  use MessageTrait, IOTrait;
  require implements RequestInterface;

  private Message\HTTPMethod $method;
  private ?string $requestTarget;
  private UriInterface $uri;

  private function createUri(mixed $uri): UriInterface {
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

  private function getHostFromUri(): string {
    $uri = $this->uri;
    $host = '';
    if ($uri is UriInterface) {
      $host  = $uri->getHost();
      $host .= ($uri->getPort() is nonnull) ? ':' . $uri->getPort() : '';
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
      if ('' !== $uri->getRawQuery()) {
        $target .= '?'. $uri->getRawQuery();
      }
    }
    return $target;
  }

  public function withRequestTarget(string $requestTarget): this {
    if (Regex\matches($requestTarget, re"#\s#")) {
      throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
    }
    $new = clone $this;
    $new->requestTarget = $requestTarget;
    return $new;
  }

  <<__Rx>>
  public function getMethod():  Message\HTTPMethod {
    return $this->method;
  }

  public function withMethod(Message\HTTPMethod $method): this {
    $new = clone $this;
    $new->method = Message\HTTPMethod::assert($method);
    return $new;
  }

  <<__Rx>>
  public function getUri(): UriInterface {
    invariant($this->uri is UriInterface, "type error.");
    return $this->uri;
  }

  public function withUri(
    UriInterface $uri,
    RequestURIOptions $options = shape('preserveHost' => false)
  ): this {
    if ($uri === $this->uri) {
      return $this;
    }
    $new = clone $this;
    $new->uri = $uri;
    if ($options['preserveHost'] === false || !$this->hasHeader('Host')) {
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
    $this->headers[$this->headerNames->at('host')] = vec[$host];
  }

  public function getBody(): IO\ReadHandle {
    $rh = $this->readHandle;
    invariant($rh is IO\ReadHandle, "handle error.");
    return $rh;
  }

  public function withBody(IO\ReadHandle $body): this {
    $new = clone $this;
    $new->readHandle = $body;
    return $new;
  }
}
