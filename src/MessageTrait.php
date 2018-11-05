<?hh // strict

namespace Ytake\Hhttp;

use type Psr\Http\Message\StreamInterface;
use namespace HH\Lib\{Str, Dict};

use function is_numeric;

trait MessageTrait {
  
  private Map<string, array<string>> $headers = Map{};
  private Map<string, string> $headerNames = Map{};

  private string $protocol = '1.1';

  private ?StreamInterface $stream;
  
  <<__Rx>>
  public function getProtocolVersion(): string {
    return $this->protocol;
  }

  public function withProtocolVersion(string $version): this {
    if ($this->protocol === $version) {
      return $this;
    }
    $new = clone $this;
    $new->protocol = $version;
    return $new;
  }

  <<__Rx>>
  public function getHeaders(): array<string, array<string>> {
    return $this->headers->toArray();
  }

  public function hasHeader(string $header): bool {
    return $this->headerNames->contains(Str\lowercase($header));
  }
  
  public function getHeader(string $header): array<string> {
    $header = Str\lowercase($header);
    if (!$this->headerNames->contains($header)) {
      return [];
    }
    return $this->headers->at($this->headerNames->at($header));
  }
  
  public function getHeaderLine(string $header): string {
    return Str\join($this->getHeader($header), ', ');
  }

  public function withHeader(string $header, mixed $value): this {
    $value = $this->validateAndTrimHeader($header, $value);
    $normalized = Str\lowercase($header);
    $new = clone $this;
    if ($this->headerNames->contains($normalized)) {
      $new->headers->remove($this->headerNames->at($normalized));
    }
    $new->headerNames->add(Pair{$normalized, $header});
    $new->headers->add(Pair{$header, $value});
    return $new;
  }

  public function withAddedHeader(string $name, mixed $value): this {
    if (!$name is string || '' === $name) {
      throw new \InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
    }
    $new = clone $this;
    $new = $new->withHeader($name, $value);
    return $new;
  }

  public function withoutHeader(string $header): this {
    $normalized = Str\lowercase($header);
    if (!$this->headerNames->contains($normalized)) {
      return $this;
    }
    $header = $this->headerNames->at($normalized);
    $new = clone $this;
    $new->headers->remove($header);
    $new->headerNames->remove($normalized);
    return $new;
  }

  public function withBody(StreamInterface $body): this {
    if ($body === $this->stream) {
      return $this;
    }
    $new = clone $this;
    $new->stream = $body;
    return $new;
  }

  private function validateAndTrimHeader(string $header, mixed $values): array<string> {
    if (1 !== \preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $header)) {
      throw new \InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
    }
    if (!\is_array($values)) {
      if ((!\is_numeric($values) && $values is string) || 1 !== \preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $values)) {
        throw new \InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
      }
      return [Str\trim((string) $values, " \t")];
    }
    if (\is_null($values) || $values == '') {
      throw new \InvalidArgumentException('Header values must be a string or an array of strings, empty array given.');
    }
    $returnValues = [];
    foreach ($values as $v) {
      if ((!\is_numeric($v) && !$v is string) || 1 !== \preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $v)) {
        throw new \InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
      }
      $returnValues[] = Str\trim((string) $v, " \t");
    }
    return $returnValues;
  }
}
