<?hh // strict

namespace Ytake\Hungrr;

use namespace HH\Lib\Str;
use namespace Ytake\Hungrr\Exception;
use namespace Facebook\Experimental\Http\Message;
use namespace HH\Lib\Experimental\IO;
use namespace HH\Lib\Experimental\Filesystem;

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
  private function open(string $filename): Filesystem\FileReadHandle {
    return Filesystem\open_read_only_non_disposable($filename);
  }

  <<__Memoize>>
  public function getStream(): IO\ReadHandle {
    return $this->open($this->filename);
  }

  private function assertDirectories(string $target): (string, string) {
    $iv = new ImmVector([$target, $this->filename]);
    $v = $iv->map($v ==> {
      $path = new Filesystem\Path($v);
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

  public function getError(): ?Message\UploadedFileError {
    return $this->error;
  }

  public function getClientFilename(): string {
    return $this->clientFilename;
  }

  public function getClientMediaType(): string {
    return $this->clientMediaType;
  }
}
