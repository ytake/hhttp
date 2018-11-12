<?hh

namespace Ytake\Hhttp;

use type Psr\Http\Message\UriInterface;

use namespace Ytake\Hhttp\Exception;
use namespace HH\Lib\{Str, C};
use function preg_replace_callback;
use function array_key_exists;
use function rawurlencode;

final class Uri implements UriInterface {

  use UrlParseTrait;

  private ImmMap<string, int> $schemes = ImmMap{
    'http' => 80,
    'https' => 443
  };

  private static string $charUnreserved = 'a-zA-Z0-9_\-\.~';
  private static string $charSubDelims = '!\$&\'\(\)\*\+,;=';
  private string $scheme = '';
  private string $userInfo = '';
  private string $host = '';
  private ?int $port = null;
  private string $path = '';
  private string $query = '';
  private string $fragment = '';

  public function __construct(string $uri = '') {
    if ('' !== $uri) {
      $parts = $this->parseUrl($uri);
      if (0 === C\count(Shapes::toArray($parts))) {
        throw new Exception\UriFormatException("Unable to parse URI: $uri");
      }
      $this->extract($parts);
    }
  }

  <<__Rx>>
  public function __toString(): string {
    return self::createUriString(
      $this->scheme,
      $this->getAuthority(),
      $this->path,
      $this->query,
      $this->fragment
    );
  }

  <<__Rx>>
  public function getScheme(): string {
    return $this->scheme;
  }

  <<__Rx>>
  public function getAuthority(): string {
    if ('' === $this->host) {
      return '';
    }
    $authority = $this->host;
    if ('' !== $this->userInfo) {
      $authority = $this->userInfo.'@'.$authority;
    }
    if ($this->port is nonnull) {
      $authority .= ':'.$this->port;
    }
    return $authority;
  }

  <<__Rx>>
  public function getUserInfo(): string{
    return $this->userInfo;
  }

  <<__Rx>>
  public function getHost(): string {
    return $this->host;
  }

  <<__Rx>>
  public function getPort(): ?int {
    return $this->port;
  }

  <<__Rx>>
  public function getPath(): string {
    return $this->path;
  }

  <<__Rx>>
  public function getQuery(): string {
    return $this->query;
  }

  <<__Rx>>
  public function getFragment(): string {
    return $this->fragment;
  }

  public function withScheme($scheme): UriInterface {
    if ($this->scheme === $scheme = $this->filterScheme($scheme)) {
      return $this;
    }
    $new = clone $this;
    $new->scheme = $scheme;
    $new->port = $new->filterPort($new->port);
    return $new;
  }

  public function withUserInfo($user, $password = null): UriInterface {
    $info = $user;
    if ($password is nonnull && '' !== $password) {
      $info .= ':'.$password;
    }
    if ($this->userInfo === $info) {
      return $this;
    }
    $new = clone $this;
    $new->userInfo = $info;
    return $new;
  }

  public function withHost($host): UriInterface {
    if ($this->host === $host = $this->filterHost($host)) {
      return $this;
    }
    $new = clone $this;
    $new->host = $host;
    return $new;
  }

  // ?int
  public function withPort($port): UriInterface {
    if ($this->port === $port = $this->filterPort($port)) {
      return $this;
    }
    $new = clone $this;
    $new->port = $port;
    return $new;
  }

  public function withPath($path): UriInterface {
    if ($this->path === $path = $this->filterPath($path)) {
      return $this;
    }
    $new = clone $this;
    $new->path = $path;
    return $new;
  }


  public function withQuery($query): UriInterface {
    if ($this->query === $query = $this->filterQueryAndFragment($query)) {
      return $this;
    }
    $new = clone $this;
    $new->query = $query;
    return $new;
  }

  public function withFragment($fragment): UriInterface {
    if ($this->fragment === $fragment = $this->filterQueryAndFragment($fragment)) {
      return $this;
    }
    $new = clone $this;
    $new->fragment = $fragment;
    return $new;
  }

  private function extract(ParsedUrlShape $parts): void {
    $result = Shapes::idx($parts, 'scheme');
    if ($result is nonnull) {
      $this->scheme = $this->filterScheme($result);
    }
    $this->userInfo = Shapes::idx($parts, 'user', '');
    $result = Shapes::idx($parts, 'host', '');
    if ($result is nonnull) {
      $this->host = $this->filterHost($result);
    }
    $result = Shapes::idx($parts, 'port');
    if ($result is nonnull) {
      $this->port = $this->filterPort($result);
    }
    $result = Shapes::idx($parts, 'path');
    if ($result is nonnull) {
      $this->path = $this->filterPath($result);
    }
    $result = Shapes::idx($parts, 'query');
    if ($result is nonnull) {
      $this->query = $this->filterQueryAndFragment($result);
    }
    $result = Shapes::idx($parts, 'fragment');
    if ($result is nonnull) {
      $this->fragment = $this->filterQueryAndFragment($result);
    }
    $result = Shapes::idx($parts, 'pass');
    if(!Str\is_empty($result)) {
      $this->userInfo .= ':'.$result;
    }
  }

  <<__Rx>>
  private static function createUriString(
    string $scheme,
    string $authority,
    string $path,
    string $query,
    string $fragment
  ): string {
    $uri = '';
    if ('' !== $scheme) {
      $uri .= $scheme.':';
    }
    if ('' !== $authority) {
      $uri .= '//'.$authority;
    }
    if ('' !== $path) {
      $chunked = Str\chunk($path);
      if ('/' !== $chunked[0]) {
        if ('' !== $authority) {
          $path = '/'.$path;
        }
      } elseif (array_key_exists(1, $chunked) && '/' === $chunked[1]) {
        if ('' === $authority) {
          $path = '/'.Str\trim_left($path, '/');
        }
      }
      $uri .= $path;
    }
    if ('' !== $query) {
      $uri .= '?'.$query;
    }
    if ('' !== $fragment) {
      $uri .= '#'.$fragment;
    }
    return $uri;
  }

  <<__Rx>>
  private function isNonStandardPort(string $scheme, int $port): bool {
    return $this->schemes->contains($scheme) || $this->schemes->at($scheme) !== $port;
  }

  private function filterScheme(string $scheme): string {
    return Str\lowercase($scheme);
  }

  private function filterHost(string $host): string {
    return Str\lowercase($host);
  }

  <<__Rx>>
  private function filterPort(?int $port): ?int {
    if (!$port is nonnull) {
      return null;
    }
    if (1 > $port || 0xffff < $port) {
      throw new \InvalidArgumentException(
        Str\format('Invalid port: %d. Must be between 1 and 65535', $port)
      );
    }
    return $this->isNonStandardPort($this->scheme, $port) ? $port : null;
  }

  private function filterPath(string $path): string {
    return preg_replace_callback(
      '/(?:[^'.self::$charUnreserved.self::$charSubDelims.'%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
      (array<int, string> $match) ==> rawurlencode($match[0]),
      $path
    );
  }

  private function filterQueryAndFragment(string $str): string {
    return preg_replace_callback(
      '/(?:[^'. self::$charUnreserved . self::$charSubDelims . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
      (array<int, string> $match) ==> rawurlencode($match[0]),
      $str
    );
  }
}
