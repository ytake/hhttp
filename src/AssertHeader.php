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

use namespace HH\Lib\{Str, Regex};

use function ord;
use function in_array;
use function gettype;
use function get_class;
use function strval;

/**
 * @see https://github.com/zendframework/zend-diactoros/blob/master/src/HeaderSecurity.php
 */
final class AssertHeader {

  private function __construct() {
  }

  public static function filter(string $value): string {
    $string = '';
    $chunked = Str\chunk($value);
    for ($i = 0; $i < Str\length($value); $i += 1) {
      $ascii = ord($chunked[$i]);
      if ($ascii === 13) {
        $lf = ord($chunked[$i + 1]);
        $ws = ord($chunked[$i + 2]);
        if ($lf === 10 && in_array($ws, [9, 32], true)) {
          $string .= $chunked[$i] . $chunked[$i + 1];
          $i += 1;
        }
        continue;
      }
      if (($ascii < 32 && $ascii !== 9)
        || $ascii === 127
        || $ascii > 254
      ) {
        continue;
      }
      $string .= $value[$i];
    }
    return $string;
  }

  public static function isValid(mixed $value): bool {
    $value  = (string) $value;
    if (Regex\matches($value, re"#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#")) {
      return false;
    }
    if (Regex\matches($value, re"/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/")) {
      return false;
    }
    return true;
  }

  public static function assertValid(mixed $value): void {
    if (!$value is string && ! is_numeric($value)) {
      throw new Exception\InvalidArgumentException(Str\format(
        'Invalid header value type; must be a string or numeric; received %s',
        (is_object($value) ? get_class($value) : gettype($value))
      ));
    }
    if (!self::isValid($value)) {
      throw new Exception\InvalidArgumentException(Str\format(
        '"%s" is not valid header value',
        strval($value)
      ));
    }
  }

  public static function assertValidName(string $name): void {
    if (!Regex\matches($name, re"/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/")) {
      throw new Exception\InvalidArgumentException(Str\format(
        '"%s" is not valid header name',
        $name
      ));
    }
  }
}
