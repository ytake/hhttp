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

use namespace HH\Lib\Experimental\IO;

use const JSON_ERROR_NONE;


class JsonResponse extends Response {
  use InjectContentTypeTrait;

  // JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
  const int DEFAULT_JSON_FLAGS = 79;

  public function __construct(
    private IO\WriteHandle $body,
    StatusCode $status = StatusCode::OK,
    dict<string, vec<string>> $headers = dict[],
    protected int $encodingOptions = self::DEFAULT_JSON_FLAGS
  ) {
    parent::__construct(
      $body,
      $status,
      /* HH_FIXME[3004] */
      $this->injectContentType('application/json', $headers),
    );
  }

  public function getEncodingOptions(): int {
    return $this->encodingOptions;
  }
}
