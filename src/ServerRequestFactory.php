<?hh // strict

namespace Ytake\Hungrr;

use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\C;

class ServerRequestFactory {

  public function createServerRequest(
    Message\HTTPMethod $method,
    Message\UriInterface $uri,
    dict<string, string> $server_params = dict[],
  ): Message\ServerRequestInterface {
    return new ServerRequest(
      $uri,
      $method,
      dict[],
      '',
      '1.1',
      $server_params
    );
  }

  public static function fromGlobals(
    dict<string, string> $server = dict[],
    dict<string, string> $post = dict[],
    dict<string, string> $query = dict[],
    dict<string, string> $cookie = dict[],
  ) : ServerRequest {
    /* HH_FIXME[2050] */
    $serverParams = (C\count($server) === 0) ? dict($_SERVER) : $server;
    /* HH_FIXME[2050] */
    $postVariables = (C\count($post) === 0) ? dict($_POST) : $post;
    /* HH_FIXME[2050] */
    $getVariables = (C\count($query) === 0) ? dict($_GET) : $query;
    /* HH_FIXME[2050] */
    $cookies = (C\count($cookie) === 0) ? dict($_COOKIE) : $cookie;
    $request = new ServerRequest(
      new Uri($serverParams['REQUEST_URI'] ?? ''),
      Message\HTTPMethod::assert($serverParams['REQUEST_METHOD'] ?? Message\HTTPMethod::GET),
      dict[],
      '',
      '1.1',
      $serverParams
    );
    return $request->withParsedBody($postVariables)
      ->withCookieParams($cookies)
      ->withQueryParams($getVariables);
  }
}
