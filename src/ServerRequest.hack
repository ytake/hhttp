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

use type Ytake\Extended\HttpMessage\ServerRequestInterface;
use type Facebook\Experimental\Http\Message\UploadedFileInterface;
use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\{Dict, IO};
use function array_key_exists;

class ServerRequest implements ServerRequestInterface {

  use RequestTrait;

  private dict<string, string> $cookieParams = dict[];
  private dict<string, string> $queryParams = dict[];
  private dict<string, UploadedFileInterface> $uploadedFiles = dict[];
  private dict<string, string> $parsedBody = dict[];
  private dict<string, mixed> $attributes = dict[];

  public function __construct(
    private Message\HTTPMethod $method,
    private Message\UriInterface $uri,
    private IO\ReadHandle $readHandle,
    dict<string, vec<string>> $headers = dict[],
    string $protocol = '1.1',
    protected dict<string, string> $serverParams = dict[]
  ) {
    $this->protocol = $protocol;
    $this->initialize($headers);
  }

  <<__Rx>>
  public function getServerParams(): dict<string, string> {
    return $this->serverParams;
  }

  public function withServerParams(dict<string, string> $values): this {
    $new = clone $this;
    $new->serverParams = $values;
    return $new;
  }

  <<__Rx>>
  public function getCookieParams(): dict<string, string> {
    return $this->cookieParams;
  }

  public function withCookieParams(dict<string, string> $cookies): this {
    $new = clone $this;
    $new->cookieParams = $cookies;
    return $new;
  }

  <<__Rx>>
  public function getQueryParams(): dict<string, string> {
    return $this->queryParams;
  }

  public function withQueryParams(dict<string, string> $query): this {
    $new = clone $this;
    $new->queryParams = $query;
    return $new;
  }

  <<__Rx>>
  public function getUploadedFiles(): dict<string, UploadedFileInterface> {
    return $this->uploadedFiles;
  }

  public function withUploadedFiles(
    dict<string, UploadedFileInterface> $uploadedFiles,
  ): this {
    $new = clone $this;
    $new->uploadedFiles = $uploadedFiles;
    return $new;
  }

  <<__Rx>>
  public function getParsedBody(): dict<string, string> {
    return $this->parsedBody;
  }

  public function withParsedBody(dict<string, string> $data): this {
    $new = clone $this;
    $new->parsedBody = $data;
    return $new;
  }

  <<__Rx>>
  public function getAttributes(): dict<string, mixed> {
    return $this->attributes;
  }

  <<__Rx>>
  public function getAttribute<T>(string $attribute, ?T $default = null): ?T {
    if (array_key_exists($attribute, $this->attributes)) {
      /* HH_FIXME[4110] */
      return $this->attributes[$attribute];
    }
    return $default;
  }

  public function withAttribute(
    string $attribute,
    mixed $value
  ): this {
    $new = clone $this;
    $new->attributes[$attribute] = $value;
    return $new;
  }

  public function withoutAttribute(
    string $attribute
  ): this {
    if (!array_key_exists($attribute, $this->attributes)) {
      return $this;
    }
    $new = clone $this;
    $new->attributes = Dict\filter_with_key($new->attributes, ($k, $_) ==> $k !== $attribute);
    return $new;
  }
}
