<?hh // strict

namespace Ytake\Hhttp;

use namespace Facebook\Experimental\Http\Message;

use namespace HH\Lib\_Private;
use namespace HH\Lib\Experimental\IO;
use namespace HH\Lib\Experimental\Filesystem;

class UploadedFile implements Message\UploadedFileInterface {
  
  public function __construct(
    protected string $filename,
    protected Message\UploadedFileError $error,
    private string $clientFilename = '',
    private string $clientMediaType = '',
  ) {
  }
  
  <<__Memoize>>
  private function open(string $filename): Filesystem\FileReadHandle {
    return _Private\fopen($filename, 'r');
  }
  
  <<__Memoize>>
  public function getStream(): IO\ReadHandle {
    return $this->open($this->filename);
  }

  public function moveTo(string $targetPath): void {
    $path = new Filesystem\Path($targetPath);
    if($path->exists()) {
      $this->getStream()->rawReadBlocking();
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
