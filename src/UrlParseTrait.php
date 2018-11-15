<?hh // strict

namespace Ytake\Hhttp;

use type Facebook\Experimental\Http\Message\UriInterface;
use function parse_url;

trait UrlParseTrait {
  require implements UriInterface;

  public function parseUrl(string $url): ParsedUrlShape {
    $parsed = parse_url($url);
    if(!$parsed) {
      return shape();
    }
    return shape(
      'scheme' => $parsed['scheme'] ?? '',
      'host' => $parsed['host'] ?? '',
      'port' => $parsed['port'] ?? null,
      'user' => $parsed['user'] ?? '',
      'pass' => $parsed['pass'] ?? '',
      'path' => $parsed['path'] ?? '',
      'query' => $parsed['query'] ?? '',
      'fragment' => $parsed['fragment'] ?? ''
    );
  }
}
