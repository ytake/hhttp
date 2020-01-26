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

use namespace HH\Lib\Str;
use namespace Ytake\Hungrr\Exception;
use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\Experimental\{File, IO};
use function php_sapi_name;
use function rename;
use function move_uploaded_file;

class UploadedFile implements Message\UploadedFileInterface {

  private bool $moved = false;

  public function __construct(
    protected string $filename,
    protected ?Message\UploadedFileError $error = null,
    private string $clientFilename = '',
    private string $clientMediaType = '',
  ) {
  }

  <<__Memoize>>
  private function open(string $filename): File\CloseableReadHandle {
    return File\open_read_only_nd($filename);
  }

  <<__Memoize>>
  public function getStream(): IO\ReadHandle {
    return $this->open($this->filename);
  }

  private function assertDirectories(string $target): (string, string) {
    $iv = new ImmVector(vec[$target, $this->filename]);
    $v = $iv->map($v ==> {
      $path = new File\Path($v);
      if(!$path->exists()) {
        throw new Exception\PathNotFoundException(
          Str\format("%s path not found.", $v)
        );
      }
      return $v;
    });
    // $target, $this->filename
    return tuple($v[0], $v[1]);
  }

  public function moveTo(string $targetPath): void {
    if(Message\UploadedFileError::isValid($this->error)) {
      throw new Exception\UploadedFileException("upload error");
    }
    list($targetPath, $filename) = $this->assertDirectories($targetPath);
    $this->moved = 'cli' === php_sapi_name() ? rename($filename, $targetPath)
      : move_uploaded_file($filename, $targetPath);
    if (false === $this->moved) {
      throw new \RuntimeException(
        Str\format('Uploaded file could not be moved to %s', $targetPath)
      );
    }
  }

  public function getSize(): ?int {
    return $this->open($this->filename)->getSize();
  }

  <<__Rx>>
  public function getError(): ?Message\UploadedFileError {
    return $this->error;
  }

  <<__Rx>>
  public function getClientFilename(): string {
    return $this->clientFilename;
  }

  <<__Rx>>
  public function getClientMediaType(): string {
    return $this->clientMediaType;
  }
}
