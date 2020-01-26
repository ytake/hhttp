/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 *
 * Copyright (c) 2018-2019 Yuuki Takezawa
 *
 */

namespace Ytake\Hungrr;

use type Facebook\Experimental\Http\Message\{
  RequestInterface,
  RequestURIOptions,
  UriInterface,
};
use namespace HH\Lib\Experimental\IO;
use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\{C, Regex};

trait RequestTrait {
  use MessageTrait;
  require implements RequestInterface;

  private Message\HTTPMethod $method;
  private ?string $requestTarget;
  private UriInterface $uri;
  private IO\ReadHandle $readHandle;

  private function initialize(
    dict<string, vec<string>> $headers = dict[],
  ): void {
    $this->setHeaders($headers);
    if (!$this->hasHeader('Host')) {
      $this->updateHostFromUri();
    }
  }

  private function getHostFromUri(): string {
    $uri = $this->uri;
    $host = '';
    $host  = $uri->getHost();
    $host .= ($uri->getPort() is nonnull) ? ':' . $uri->getPort() : '';
    return $host;
  }

  public function getRequestTarget(): string {
    if ($this->requestTarget is nonnull) {
      return $this->requestTarget;
    }
    $target = '';
    $uri = $this->uri;
    $target = $uri->getPath();
    if ('' === $target) {
      $target = '/';
    }
    if ('' !== $uri->getRawQuery()) {
      $target .= '?'. $uri->getRawQuery();
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
  public function getMethod(): Message\HTTPMethod {
    return $this->method;
  }

  public function withMethod(Message\HTTPMethod $method): this {
    $new = clone $this;
    $new->method = Message\HTTPMethod::assert($method);
    return $new;
  }

  <<__Rx>>
  public function getUri(): UriInterface {
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
    $host = $uri->getHost();
    if ('' === $host) {
      return;
    }
    $port = $uri->getPort();
    if (null !== $port) {
      $host .= ':'.$port;
    }
    if (!C\contains_key($this->headerNames, 'host')) {
      $this->headerNames['host'] = 'Host';
    }
    $this->headers[$this->headerNames['host']] = vec[$host];
  }

  <<__Rx>>
  public function getBody(): IO\ReadHandle {
    return $this->readHandle;
  }

  public function withBody(IO\ReadHandle $body): this {
    $new = clone $this;
    $new->readHandle = $body;
    return $new;
  }
}
