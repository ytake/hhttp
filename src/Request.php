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

use type Facebook\Experimental\Http\Message\RequestInterface;

use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\Experimental\IO;

class Request implements RequestInterface {
  use RequestTrait;

  public function __construct(
    private Message\HTTPMethod $method,
    private Message\UriInterface $uri,
    private IO\ReadHandle $body,
    dict<string, vec<string>> $headers = dict[],
    string $protocol = '1.1'
  ) {
    $this->initialize($headers);
    $this->protocol = $protocol;
  }
}
