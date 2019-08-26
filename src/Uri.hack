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

use type Facebook\Experimental\Http\Message\UriInterface;
use namespace Ytake\Hungrr\Exception;
use namespace HH\Lib\{Str, C, Dict};
use function preg_replace_callback;
use function http_build_query;
use function rawurlencode;
use function parse_str;

final class Uri implements UriInterface {

  use UrlParseTrait;

  private ImmMap<string, int> $schemes = ImmMap{
    'http' => 80,
    'https' => 443
  };

  private static string $charUnreserved = 'a-zA-Z0-9_\-\.~';
  private static string $charSubDelims = '!\$&\'\(\)\*\+,;=';
  private string $scheme = '';
  private string $user = '';
  private string $password = '';
  private string $host = '';
  private ?int $port = null;
  private string $path = '';
  private string $rawQuery = '';
  private dict<string, string> $query = dict[];
  private string $fragment = '';

  public function __construct(string $uri = '') {
    if ('' !== $uri) {
      $parts = $this->parseUrl($uri);
      if (0 === C\count(Shapes::toArray($parts))) {
        throw new Exception\UriFormatException("Unable to parse URI: ".$uri);
      }
      $this->extract($parts);
    }
  }

  public function toString(): string {
    return self::createUriString(
      $this->scheme,
      $this->getAuthority(),
      $this->path,
      $this->rawQuery,
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
    $userInfo = '';
    if ($this->user !== '') {
      $userInfo = $this->user;
      if ($this->password !== '') {
        $userInfo .= Str\format(':%s', $this->password);
      }
      $authority = Str\format('%s@%s', $userInfo, $authority);
    }
    if ($this->port is nonnull) {
      $authority .= ':'.$this->port;
    }
    return $authority;
  }

  <<__Rx>>
  public function getUserInfo(): shape('user' => string, 'pass' => string) {
    return shape(
      'user' => $this->user,
      'pass' => $this->password,
    );
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
  public function getQuery(): dict<string, string> {
    return $this->query;
  }

  <<__Rx>>
  public function getRawQuery(): string {
    return $this->rawQuery;
  }

  <<__Rx>>
  public function getFragment(): string {
    return $this->fragment;
  }

  public function withScheme(string $scheme): this {
    if ($this->scheme === $scheme = $this->filterScheme($scheme)) {
      return $this;
    }
    $new = clone $this;
    $new->scheme = $scheme;
    $new->port = $new->filterPort($new->port);
    return $new;
  }

  public function withUserInfo(string $user, string $password): this {
    if ($this->user === $user && $this->password === $password) {
      return $this;
    }
    $new = clone $this;
    $new->user = $user;
    $new->password = $password;
    return $new;
  }

  public function withHost(string $host): this {
    if ($this->host === $host = $this->filterHost($host)) {
      return $this;
    }
    $new = clone $this;
    $new->host = $host;
    return $new;
  }

  public function withPort(?int $port = null): this {
    if ($this->port === $port = $this->filterPort($port)) {
      return $this;
    }
    $new = clone $this;
    $new->port = $port;
    return $new;
  }

  public function withPath(string $path): this {
    if ($this->path === $path = $this->filterPath($path)) {
      return $this;
    }
    $new = clone $this;
    $new->path = $path;
    return $new;
  }

  public function withQuery(dict<string, string> $query): this {
    if ($this->query === $query) {
      return $this;
    }
    $new = clone $this;
    $new->query = $query;

    return $new;
  }

  public function withRawQuery(string $query): this {
    if ($this->query === $query = $this->filterQueryAndFragment($query)) {
      return $this;
    }
    $new = clone $this;
    $new->rawQuery = $query;
    return $new;
  }

  public function withFragment(string $fragment): this {
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
    $this->user = Shapes::idx($parts, 'user', '');
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
      $this->rawQuery = $this->filterQueryAndFragment($result);
    }
    $result = Shapes::idx($parts, 'fragment');
    if ($result is nonnull) {
      $this->fragment = $this->filterQueryAndFragment($result);
    }
    $this->password = Shapes::idx($parts, 'pass', '');
    $out = [];
    parse_str($this->rawQuery, &$out);
    $this->query = dict($out);
  }

  private static function createUriString(
    string $scheme,
    string $authority,
    string $path,
    string $rawQuery,
    dict<string, string> $query,
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
      } elseif (C\contains_key($chunked, 1) && '/' === $chunked[1]) {
        if ('' === $authority) {
          $path = '/'.Str\trim_left($path, '/');
        }
      }
      $uri .= $path;
    }
    $out = [];
    $mergeQuery = dict[];
    parse_str($rawQuery, &$out);
    $mergeQuery = Dict\merge($query, dict($out));
    if(C\count($mergeQuery)) {
      $uri .= '?'. http_build_query($mergeQuery);
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
