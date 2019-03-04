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

use namespace HH\Lib\C;
use namespace HH\Lib\Experimental\IO;
use namespace Facebook\Experimental\Http\Message;

class ServerRequestFactory {

  public function createServerRequest(
    Message\HTTPMethod $method,
    Message\UriInterface $uri,
    dict<string, string> $server_params = dict[],
  ): Message\ServerRequestInterface {
    return new ServerRequest(
      $method,
      $uri,
      IO\request_input(),
      dict[],
      '1.1',
      $server_params
    );
  }

  public static function fromGlobals(
    IO\ReadHandle $readHandle = IO\request_input(),
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
      Message\HTTPMethod::assert($serverParams['REQUEST_METHOD'] ?? Message\HTTPMethod::GET),
      new Uri($serverParams['REQUEST_URI'] ?? ''),
      $readHandle,
      dict[],
      '1.1',
      $serverParams
    );
    return $request->withParsedBody($postVariables)
      ->withCookieParams($cookies)
      ->withQueryParams($getVariables);
  }
}
