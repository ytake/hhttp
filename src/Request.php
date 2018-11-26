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

namespace Ytake\Hungrr;

use type Facebook\Experimental\Http\Message\UriInterface;
use type Facebook\Experimental\Http\Message\RequestInterface;
use namespace Facebook\Experimental\Http\Message;

class Request implements RequestInterface {
  use RequestTrait;

  public function __construct(
    mixed $uri,
    Message\HTTPMethod $method = Message\HTTPMethod::GET,
    dict<string, vec<string>> $headers = dict[],
    string $body = '',
    protected string $protocol = '1.1'
  ) {
    $this->method = $method;
    $this->initialize($uri, $headers, $body);
  }
}
