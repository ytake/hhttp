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

use type Facebook\Experimental\Http\Message\ServerRequestInterface;
use type Facebook\Experimental\Http\Message\UploadedFileInterface;

use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\Experimental\IO;

class ServerRequest implements ServerRequestInterface {

  use RequestTrait;

  private dict<string, string> $cookieParams = dict[];
  private dict<string, string> $queryParams = dict[];
  private dict<string, UploadedFileInterface> $uploadedFiles = dict[];
  private dict<string, string> $parsedBody = dict[];

  public function __construct(
    private Message\HTTPMethod $method,
    private Message\UriInterface $uri,
    private IO\ReadHandle $body,
    dict<string, vec<string>> $headers = dict[],
    string $protocol = '1.1',
    protected dict<string, string> $serverParams = dict[]
  ) {
    $this->protocol = $protocol;
    $this->initialize($headers);
  }

  public function getServerParams(): dict<string, string> {
    return $this->serverParams;
  }

  public function withServerParams(dict<string, string> $values): this {
    $new = clone $this;
    $new->serverParams = $values;
    return $new;
  }

  public function getCookieParams(): dict<string, string> {
    return $this->cookieParams;
  }

  public function withCookieParams(dict<string, string> $cookies): this {
    $new = clone $this;
    $new->cookieParams = $cookies;
    return $new;
  }

  public function getQueryParams(): dict<string, string> {
    return $this->queryParams;
  }

  public function withQueryParams(dict<string, string> $query): this {
    $new = clone $this;
    $new->queryParams = $query;
    return $new;
  }

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

  public function getParsedBody(): dict<string, string> {
    return $this->parsedBody;
  }

  public function withParsedBody(dict<string, string> $data): this {
    $new = clone $this;
    $new->parsedBody = $data;
    return $new;
  }
}
