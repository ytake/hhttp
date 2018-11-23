<?hh // strict

namespace Ytake\Hungrr;

use type Facebook\Experimental\Http\Message\{ServerRequestInterface, UploadedFileInterface, UriInterface};

use namespace Facebook\Experimental\Http\Message;

class ServerRequest implements ServerRequestInterface {

  use RequestTrait;

  private dict<string, string> $cookieParams = dict[];
  private dict<string, string> $queryParams = dict[];
  private dict<string, UploadedFileInterface> $uploadedFiles = dict[];
  private dict<string, string> $parsedBody = dict[];

  public function __construct(
    mixed $uri,
    private Message\HTTPMethod $method = Message\HTTPMethod::GET,
    dict<string, vec<string>> $headers = dict[],
    string $body = '',
    string $version = '1.1',
    protected dict<string, string> $serverParams = dict[]
  ) {
    if ($uri is string) {
      $uri = new Uri($uri);
    }
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

  public function getServerParams(): dict<string, string> {
    return $this->serverParams;
  }

  public function withServerParams(dict<string, string> $values): this {
    $new = clone $this;
    $new->serverParams = $values;
    return $new;
  }

  public function getCookieParams(): dict<string, string> {
    return $this->cookieParams;
  }

  public function withCookieParams(dict<string, string> $cookies): this {
    $new = clone $this;
    $new->cookieParams = $cookies;
    return $new;
  }

  public function getQueryParams(): dict<string, string> {
    return $this->queryParams;
  }

  public function withQueryParams(dict<string, string> $query): this {
    $new = clone $this;
    $new->queryParams = $query;
    return $new;
  }

  public function getUploadedFiles(): dict<string, UploadedFileInterface> {
    return $this->uploadedFiles;
  }

  public function withUploadedFiles(
    dict<string, UploadedFileInterface> $uploadedFiles,
  ): this {
    $new = clone $this;
    $new->uploadedFiles = $uploadedFiles;
    return $new;
  }

  public function getParsedBody(): dict<string, string> {
    return $this->parsedBody;
  }

  public function withParsedBody(dict<string, string> $data): this {
    $new = clone $this;
    $new->parsedBody = $data;
    return $new;
  }
}
