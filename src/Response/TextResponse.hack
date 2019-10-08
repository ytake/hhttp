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

namespace Ytake\Hungrr\Response;

use type Ytake\Hungrr\{Response, StatusCode};
use namespace HH\Lib\Experimental\IO;

class TextResponse extends Response {
  use InjectContentTypeTrait;

  public function __construct(
    private IO\WriteHandle $body,
    StatusCode $status = StatusCode::OK,
    dict<string, vec<string>> $headers = dict[]
  ) {
    parent::__construct(
      $body,
      $status,
      /* HH_FIXME[3004] */
      $this->injectContentType('text/plain; charset=utf-8', $headers),
    );
  }
}
