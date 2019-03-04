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
