<?hh // strict

namespace Ytake\Hhttp;

use type Psr\Http\Message\StreamInterface;

use function fopen;
use function fwrite;
use function array_key_exists;
use function stream_get_meta_data;
use function stream_get_contents;

final class Stream implements StreamInterface {

  private ?resource $stream;
  private ?bool $seekable;
  private ?bool $readable;
  private ?bool $writable;
  private mixed $uri;
  private ?int $size;

  private static ImmMap<string, ImmMap<string, bool>> $readWriteHash = ImmMap {
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

  public static function create(mixed $body = ''): StreamInterface {
    if ($body is StreamInterface) {
      return $body;
    }

    if ($body is string) {
      $resource = fopen('php://temp', 'rw+');
      fwrite($resource, $body);
      $body = $resource;
    }

    if ($body is resource) {
      $obj = new self();
      $obj->stream = $body;
      $meta = stream_get_meta_data($obj->stream);
      $obj->seekable = $meta['seekable'];
      $obj->readable = self::$readWriteHash->at('read') |> $$->contains($meta['mode']);
      $obj->writable = self::$readWriteHash->at('write') |> $$->contains($meta['mode']);
      $obj->uri = $obj->getMetadata('uri');
      return $obj;
    }
    throw new \InvalidArgumentException(
      'First argument to Stream::create() must be a string, resource or StreamInterface.'
    );
  }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (\is_resource($this->stream)) {
                \fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if (null !== $this->size) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            \clearstatcache(true, $this->uri);
        }

        $stats = \fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    public function tell(): int
    {
        if (false === $result = \ftell($this->stream)) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function eof(): bool
    {
        return !$this->stream || \feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = \SEEK_SET): void
    {
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        } elseif (\fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position '.$offset.' with whence '.\var_export($whence, true));
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write($string): int
    {
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;

        if (false === $result = \fwrite($this->stream, $string)) {
            throw new \RuntimeException('Unable to write to stream');
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
    if (!$this->readable is nonnull) {
      throw new \RuntimeException('Cannot read from non-readable stream');
    }
    return \fread($this->stream, $length);
  }

  public function getContents(): string {
    if ($this->stream is resource) {
      throw new \RuntimeException('Unable to read stream contents');
    }
    if (false === $contents = stream_get_contents($this->stream)) {
      throw new \RuntimeException('Unable to read stream contents');
    }
    return $contents;
  }

  public function getMetadata($key = null): mixed {
    if ($this->stream is resource) {
      return $key ? null : [];
    } elseif (!$key is nonnull) {
      return stream_get_meta_data($this->stream);
    }
    $meta = stream_get_meta_data($this->stream);
    return array_key_exists($key, $meta) ? $meta[$key] : null;
  }
}
