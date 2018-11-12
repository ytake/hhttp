<?hh

namespace Ytake\Hhttp;

use type RuntimeException;
use type Psr\Http\Message\StreamInterface;

use function feof;
use function fopen;
use function ftell;
use function fseek;
use function fstat;
use function fclose;
use function fread;
use function fwrite;
use function rewind;
use function is_resource;
use function array_key_exists;
use function stream_get_meta_data;
use function stream_get_contents;
use function clearstatcache;
use function var_export;

use const SEEK_SET;

final class Stream implements StreamInterface {

  private ?resource $stream;
  private bool $seekable = false;
  private bool $readable = false;
  private bool $writable = false;
  private mixed $uri;
  private ?int $size;

  private ImmMap<string, ImmMap<string, bool>> $readWriteHash = ImmMap {
    'read' => ImmMap{
      'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
      'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
      'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
      'x+t' => true, 'c+t' => true, 'a+' => true,
    },
    'write' => ImmMap{
      'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
      'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
      'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
      'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
    },
  };

  public function __construct(mixed $body, string $mode = 'rw+') {
    if ($body is string) {
      $resource = fopen($body, $mode);
      fwrite($resource, $body);
      rewind($resource);
      $body = $resource;
    }
    if ($body is resource) {
      $this->stream = $body;
      $meta = stream_get_meta_data($this->stream);
      $this->seekable = $meta['seekable'];
      $this->readable = $this->readWriteHash->at('read') |> $$->contains($meta['mode']);
      $this->writable = $this->readWriteHash->at('write') |> $$->contains($meta['mode']);
      $this->uri = $this->getMetadata('uri');
    }
  }

  public function __toString(): string {
    try {
      if ($this->isSeekable()) {
        $this->seek(0);
      }
      return $this->getContents();
    } catch (\Exception $e) {
      return '';
    }
  }

  public function close(): void {
    if (is_resource($this->stream)) {
      fclose($this->stream);
    }
    $this->detach();
  }

  public function detach(): ?resource {
    if (!$this->stream is nonnull) {
      return null;
    }
    $result = $this->stream;
    $this->stream = null;
    $this->size = $this->uri = null;
    $this->readable = $this->writable = $this->seekable = false;
    return $result;
  }

  public function getSize(): ?int {
    if (null !== $this->size) {
      return $this->size;
    }
    if (!$this->stream is nonnull) {
      return null;
    }
    if ($this->uri is string) {
      clearstatcache();
    }
    $stats = fstat($this->stream);
    if (array_key_exists('size', $stats)) {
      $this->size = $stats['size'];
      return $this->size;
    }
    return null;
  }

  public function tell(): int {
    if (!$this->stream is nonnull || false === $result = ftell($this->stream)) {
      throw new RuntimeException('Unable to determine stream position');
    }
    return $result;
  }

  public function eof(): bool {
    return !$this->stream || feof($this->stream);
  }

  public function isSeekable(): bool {
    return $this->seekable;
  }

  public function seek($offset, $whence = \SEEK_SET): void {
    if (!$this->seekable) {
      throw new \RuntimeException('Stream is not seekable');
    } elseif (fseek($this->stream, $offset, $whence) === -1) {
      throw new \RuntimeException(
        'Unable to seek to stream position '.$offset.' with whence '.var_export($whence, true)
      );
    }
  }

  public function rewind(): void {
    $this->seek(0);
  }

  public function isWritable(): bool {
    return $this->writable;
  }

  public function write($string): int {
    if (!$this->writable) {
      throw new RuntimeException('Cannot write to a non-writable stream');
    }
    $this->size = null;
    if (false === $result = fwrite($this->stream, $string)) {
      throw new RuntimeException('Unable to write to stream');
    }
    return $result;
  }

  <<__Rx>>
  public function isReadable(): bool {
    if($this->readable is nonnull) {
      return $this->readable;
    }
    return false;
  }

  public function read($length): string {
    if (!$this->readable is nonnull || !$this->readable) {
      throw new \RuntimeException('Cannot read from non-readable stream');
    }
    return fread($this->stream, $length);
  }

  public function getContents(): string {
    if (!$this->stream is resource) {
      throw new \RuntimeException('Unable to read stream contents');
    }
    if (false === $contents = stream_get_contents($this->stream)) {
      throw new \RuntimeException('Unable to read stream contents');
    }
    return $contents;
  }

  public function getMetadata($key = null): mixed {
    if (!$this->stream is resource) {
      return $key ? null : [];
    } elseif (!$key is nonnull) {
      return stream_get_meta_data($this->stream);
    }
    return stream_get_meta_data($this->stream)
    |> array_key_exists($key, $$) ? $$[$key] : null;
  }
}
