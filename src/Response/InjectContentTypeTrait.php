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
use namespace HH\Lib\{Vec, Str, C};

trait InjectContentTypeTrait {
  require extends Response;

  private function injectContentType(
    string $contentType,
    dict<string, vec<string>> $headers
  ): dict<string, vec<string>> {
    $hasContentType = C\reduce(
      Vec\keys($headers),
      ($carry, $item) ==> $carry ?: (Str\lowercase($item) === 'content-type'),
      false
    );
    if (!$hasContentType) {
      $headers['content-type'] = vec[$contentType];
    }
    return $headers;
  }
}
