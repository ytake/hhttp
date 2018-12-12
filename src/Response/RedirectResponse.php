<?hh // strict

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
 * Copyright (c) 2018 Yuuki Takezawa
 *
 */

namespace Ytake\Hungrr\Response;

use type Ytake\Hungrr\Response;
use type Ytake\Hungrr\StatusCode;
use type Facebook\Experimental\Http\Message\UriInterface;

use namespace Ytake\Hungrr\Exception;
use namespace HH\Lib\Str;
use namespace HH\Lib\Experimental\IO;

use function is_object;
use function get_class;
use function gettype;

class RedirectResponse extends Response {

  public function __construct(
    UriInterface $uri,
    StatusCode $status = StatusCode::FOUND,
    dict<string, vec<string>> $headers = dict[]
  ) {
    $headers['location'] = vec[(string) $uri];
    parent::__construct(
      IO\request_input(),
      $status,
      $headers,
    );
  }
}
