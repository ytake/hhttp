<?hh // strict

namespace Ytake\Hhttp;

use namespace HH\Lib\{Str, Regex, Vec, C, Dict};


trait MessageTrait {

  private dict<string, vec<string>> $headers = dict[];
  private Map<string, string> $headerNames = Map{};

  private string $protocol = '1.1';

  protected function extractHeaders(string $header, vec<string> $value): void {
    $nh = Str\lowercase($header);
    if ($this->headerNames->contains($nh)) {
      $header = $this->headerNames[$nh];
      $this->headers[$header] =  Vec\concat($this->headers[$header], $value);
      return;
    }
    $this->headerNames[$nh] = $header;
    $this->headers[$header] = $value;
  }

  private function setHeaders(Map<string, vec<string>> $originalHeaders) : void {
    foreach ($originalHeaders as $header => $value) {
      $this->assertHeader($header);
      $this->extractHeaders(
        $header,
        $this->filterHeaderValue($this->validateAndTrimHeader($header, $value))
      );
    }
  }

  private function assertHeader(string $name) : void {
    AssertHeader::assertValidName($name);
  }

  private function filterHeaderValue(vec<string> $values): vec<string> {
    if (!C\count($values)) {
      throw new Exception\InvalidArgumentException(
        'Invalid header value: must be a vec<string>; cannot be an empty vec[]'
      );
    }
    return Vec\map($values, ($t) ==> {
      AssertHeader::assertValid($t);
      return Str\trim($t, " \t");
    });
  }

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

  public function getHeaders(): dict<string, vec<string>> {
    return $this->headers;
  }

  public function hasHeader(string $header): bool {
    return $this->headerNames->contains(Str\lowercase($header));
  }

  public function getHeader(string $header): vec<string> {
    $lowHeader = Str\lowercase($header);
    if (!$this->headerNames->contains($lowHeader)) {
      return vec[];
    }
    return $this->headers[$this->headerNames->at($lowHeader)];
  }

  public function getHeaderLine(string $header): string {
    return Str\join($this->getHeader($header), ', ');
  }

  public function withHeader(string $header, vec<string> $value): this {
    $normalized = Str\lowercase($header);
    $new = clone $this;
    if ($this->headerNames->contains($normalized)) {
      $new->headers = Dict\filter_keys(
        $new->headers,
        ($k) ==> $k !== $this->headerNames->at($normalized)
      );
    }
    $new->headerNames->add(Pair{$normalized, $header});
    $new->headers[$header] = $this->filterHeaderValue($this->validateAndTrimHeader($header, $value));
    return $new;
  }

  public function withHeaderLine(string $name, string $value): this {
    return $this->withHeader($name, Str\split($value, ','));
  }

  public function withAddedHeader(string $name, vec<string> $value): this {
    if ('' === $name) {
      throw new \InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
    }
    $new = clone $this;
    $new->setHeaders(Map{$name => $value});
    return $new;
  }

  public function withAddedHeaderLine(string $name, string $value): this {
    return $this->withAddedHeader($name, Str\split($value, ','));
  }

  public function withoutHeader(string $header): this {
    $normalized = Str\lowercase($header);
    if (!$this->headerNames->contains($normalized)) {
      return $this;
    }
    $header = $this->headerNames->at($normalized);
    $new = clone $this;
    $m = new Map($new->headers);
    $new->headers = dict($m->removeKey($header));
    $nh = new Map($new->headerNames);
    $new->headerNames = $nh->removeKey($normalized);
    return $new;
  }

  private function validateAndTrimHeader(string $header, vec<string> $values): vec<string> {
    if (!Regex\matches($header, re"@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@")) {
      throw new Exception\InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
    }
    return Vec\map($values, ($r) ==> {
      if (!Regex\matches($r, re"@^[ \t\x21-\x7E\x80-\xFF]*$@")) {
        throw new \InvalidArgumentException('Header values must be RFC 7230 compatible string.');
      }
      return Str\trim($r, " \t");
    });
  }
}
