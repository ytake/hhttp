<?hh // strict

namespace Ytake\Hungrr;

use namespace HH\Lib\{Str, Regex, Vec, C, Dict};
use function array_key_exists;

trait MessageTrait {

  private dict<string, vec<string>> $headers = dict[];
  private dict<string, string> $headerNames = dict[];

  private string $protocol = '1.1';

  protected function extractHeaders(string $header, vec<string> $value): void {
    $nh = Str\lowercase($header);
    if (array_key_exists($nh, $this->headerNames)) {
      $header = $this->headerNames[$nh];
      $this->headers[$header] =  Vec\concat($this->headers[$header], $value);
      return;
    }
    $this->headerNames[$nh] = $header;
    $this->headers[$header] = $value;
  }

  private function setHeaders(dict<string, vec<string>> $originalHeaders): void {
    foreach ($originalHeaders as $header => $value) {
      $this->assertHeader($header);
      $this->extractHeaders(
        $header,
        $this->filterHeaderValue($this->validateAndTrimHeader($header, $value))
      );
    }
  }

  private function assertHeader(string $name): void {
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
    return array_key_exists(Str\lowercase($header), $this->headerNames);
  }

  public function getHeader(string $header): vec<string> {
    $lowHeader = Str\lowercase($header);
    if (!array_key_exists($lowHeader, $this->headerNames)) {
      return vec[];
    }
    return $this->headers[$this->headerNames[$lowHeader]];
  }

  public function getHeaderLine(string $header): string {
    return Str\join($this->getHeader($header), ', ');
  }

  public function withHeader(string $header, vec<string> $value): this {
    $lowHeader = Str\lowercase($header);
    $new = clone $this;
    if (array_key_exists($lowHeader, $this->headerNames)) {
      $new->headers = Dict\filter_keys(
        $new->headers,
        ($k) ==> $k !== $this->headerNames[$lowHeader]
      );
    }
    $new->headerNames[$lowHeader] = $header;
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
    $new->setHeaders(dict[$name => $value]);
    return $new;
  }

  public function withAddedHeaderLine(string $name, string $value): this {
    return $this->withAddedHeader($name, Str\split($value, ','));
  }

  public function withoutHeader(string $header): this {
    $lowHeader = Str\lowercase($header);
    if (!array_key_exists($lowHeader, $this->headerNames)) {
      return $this;
    }
    $header = $this->headerNames[$lowHeader];
    $new = clone $this;
    $m = new Map($new->headers);
    $new->headers = dict($m->removeKey($header));
    $nh = new Map($new->headerNames);
    $new->headerNames = dict($nh->removeKey($lowHeader));
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
