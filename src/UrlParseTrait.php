<?hh // strict

namespace Ytrake\Hhttp;

use function parse_url;

trait UrlParseTrait {
  require implements \Psr\Http\Message\UriInterface;
  
  public function parseUrl(string $url): ParsedUrlShape {
    $parsed = parse_url($url);
    if(!$parsed) {
      return shape();
    }
    return shape(
      'schema' => $parsed['schema'],
      'host' => $parsed['host'],
      'port' => $parsed['port'],
      'user' => $parsed['user'],
      'pass' => $parsed['pass'],
      'path' => $parsed['path'],
      'query' => $parsed['query'],
      'fragment' => $parsed['fragment']
    );
  }
}
