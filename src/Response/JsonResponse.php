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

use namespace Ytake\Hungrr\Exception;
use namespace HH\Lib\Str;
use namespace HH\Lib\Experimental\IO;

use const JSON_ERROR_NONE;

use function json_encode;
use function json_last_error;
use function json_last_error_msg;

class JsonResponse extends Response {
  use InjectContentTypeTrait;

  // JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
  const int DEFAULT_JSON_FLAGS = 79;

  public function __construct(
    protected ImmMap<mixed, mixed> $payload,
    StatusCode $status = StatusCode::OK,
    dict<string, vec<string>> $headers = dict[],
    protected int $encodingOptions = self::DEFAULT_JSON_FLAGS
  ) {
    list($read, $write) = IO\pipe_non_disposable();
    $write->rawWriteBlocking(
      $this->jsonEncode($payload, $this->encodingOptions),
    );
    parent::__construct(
      $write,
      $status,
      /* HH_FIXME[3004] */
      $this->injectContentType('application/json', $headers),
    );
  }

  public function withEncodingOptions(int $encodingOptions): this {
    $new = clone $this;
    $new->encodingOptions = $encodingOptions;
    return $this->updateBodyFor($new);
  }

  private function jsonEncode(
    ImmMap<mixed, mixed> $payload,
    int $encodingOptions
  ): string {
    json_encode(null);
    $json = json_encode($payload, $encodingOptions);
    if (JSON_ERROR_NONE !== json_last_error()) {
      throw new Exception\InvalidArgumentException(Str\format(
        'Unable to encode data to JSON in %s: %s',
        __CLASS__,
        json_last_error_msg()
      ));
    }
    return $json;
  }

  public function getPayload(): ImmMap<mixed, mixed> {
    return $this->payload;
  }

  public function withPayload(ImmMap<mixed, mixed> $payload): this {
    $new = clone $this;
    $new->payload = $payload;
    return $this->updateBodyFor($new);
  }

  public function getEncodingOptions(): int {
    return $this->encodingOptions;
  }

  private function updateBodyFor(this $toUpdate): this {
    $body = $this->jsonEncode($toUpdate->payload, $toUpdate->encodingOptions);
    $toUpdate->getBody()->rawWriteBlocking($body);
    return $toUpdate;
  }
}
