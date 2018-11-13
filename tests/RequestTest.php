<?hh // strict 

use type Ytake\Hhttp\Uri;
use type Ytake\Hhttp\Request;
use type Ytake\Hhttp\HttpMethod;
use type Facebook\HackTest\HackTest;
use type Psr\Http\Message\StreamInterface;

use function Facebook\FBExpect\expect;

final class RequestTest extends HackTest {

  public function testRequestUriMayBeString(): void {
    $r = new Request('/');
    expect((string) $r->getUri())->toBeSame('/');
  }

  public function testRequestUriMayBeUri(): void {
    $uri = new Uri('/');
    $r = new Request($uri);
    expect($r->getUri())->toBeSame($uri);
  }
  
  <<ExpectedException(\InvalidArgumentException::class), ExpectedExceptionMessage('Unable to parse URI: ///')>>
  public function testValidateRequestUri(): void {
    new Request('///');
  }

  public function testCanConstructWithBody(): void {
    $r = new Request('/', HttpMethod::GET, 'baz', Map{});
    expect($r->getBody())->toBeInstanceOf(StreamInterface::class);
    expect((string) $r->getBody())->toBeSame('baz');
  }

  public function testNullBody(): void{
    $r = new Request('/', HttpMethod::GET, null, Map{});
    expect($r->getBody())->toBeInstanceOf(StreamInterface::class);
    expect((string) $r->getBody())->toBeSame('');
  }

  public function testFalseyBody(): void {
    $r = new Request('/', HttpMethod::GET, '0', Map{});
    expect($r->getBody())->toBeInstanceOf(StreamInterface::class);
    expect((string) $r->getBody())->toBeSame('0');
  }

  public function testWithUri(): void {
    $r1 = new Request('/');
    $u1 = $r1->getUri();
    $u2 = new Uri('http://www.example.com');
    $r2 = $r1->withUri($u2);
    expect($r2)->toNotBeSame($r1);
    expect($r2->getUri())->toBeSame($u2);
    expect($r1->getUri())->toBeSame($u1);
  }

  public function testSameInstanceWhenSameUri(): void {
    $r1 = new Request('http://foo.com');
    $r2 = $r1->withUri($r1->getUri());
    expect($r2)->toBeSame($r1);
  }

  public function testWithRequestTarget(): void {
    $r1 = new Request('/');
    $r2 = $r1->withRequestTarget('*');
    expect($r2->getRequestTarget())->toBeSame('*');
    expect($r1->getRequestTarget())->toBeSame('/');
  }

  <<ExpectedException(\InvalidArgumentException::class), ExpectedExceptionMessage('Invalid request target provided; cannot contain whitespace')>>
  public function testRequestTargetDoesNotAllowSpaces(): void {
    $r1 = new Request('/');
    $r1->withRequestTarget('/foo bar');
  }

  public function testRequestTargetDefaultsToSlash(): void {
    $r1 = new Request('');
    expect($r1->getRequestTarget())->toBeSame('/');
    $r2 = new Request('*');
    expect($r2->getRequestTarget())->toBeSame('*');
    $r3 = new Request('http://foo.com/bar baz/');
    expect($r3->getRequestTarget())->toBeSame('/bar%20baz/');
  }

  public function testBuildsRequestTarget(): void {
    $r1 = new Request('http://foo.com/baz?bar=bam');
    expect($r1->getRequestTarget())->toBeSame('/baz?bar=bam');
  }

  public function testBuildsRequestTargetWithFalseyQuery(): void {
    $r1 = new Request('http://foo.com/baz?0');
    expect($r1->getRequestTarget())->toBeSame('/baz?0');
  }

  public function testHostIsAddedFirst(): void {
    $r = new Request('http://foo.com/baz?bar=bam', HttpMethod::GET, 'php://temp', Map{'Foo' => varray['Bar']});
    expect($r->getHeaders())->toContainKey('Host');
    expect($r->getHeaders())->toContainKey('Foo');
  }

  public function testCanGetHeaderAsCsv(): void {
    $r = new Request(
      'http://foo.com/baz?bar=bam', 
      HttpMethod::GET, 
      'php://temp', 
      Map{
        'Foo' => varray['a', 'b', 'c'],
      }
    );
    expect($r->getHeaderLine('Foo'))->toBeSame('a, b, c');
    expect($r->getHeaderLine('Bar'))->toBeSame('');
  }

  public function testHostIsNotOverwrittenWhenPreservingHost(): void {
    $r = new Request('http://foo.com/baz?bar=bam', HttpMethod::GET, 'php://temp', Map{'Host' => varray['a.com']});
    expect($r->getHeaders())->toBeSame(['Host' => ['a.com']]);
    $r2 = $r->withUri(new Uri('http://www.foo.com/bar'), true);
    expect($r2->getHeaderLine('Host'))->toBeSame('a.com');
  }

  public function testOverridesHostWithUri(): void {
    $r = new Request('http://foo.com/baz?bar=bam');
    expect($r->getHeaders())->toBeSame(['Host' => ['foo.com']]);
    $r2 = $r->withUri(new Uri('http://www.baz.com/bar'));
    expect($r2->getHeaderLine('Host'))->toBeSame('www.baz.com');
  }

  public function testAggregatesHeaders(): void {
    $r = new Request('', HttpMethod::GET, 'php://temp', Map{
      'ZOO' => ['zoobar'],
      'zoo' => ['foobar', 'zoobar'],
    });
    expect($r->getHeaders())->toBeSame(['ZOO' => ['zoobar', 'foobar', 'zoobar']]);
    expect($r->getHeaderLine('zoo'))->toBeSame('zoobar, foobar, zoobar');
  }

  public function testSupportNumericHeaders(): void {
    $r = new Request('', HttpMethod::GET, 'php://temp', Map{
      'Content-Length' => varray['200'],
    });
    expect($r->getHeaders())->toBeSame(['Content-Length' => ['200']]);
    expect($r->getHeaderLine('Content-Length'))->toBeSame('200');
  }

  public function testAddsPortToHeader(): void {
    $r = new Request('http://foo.com:8124/bar');
    expect($r->getHeaderLine('host'))->toBeSame('foo.com:8124');
  }

  public function testAddsPortToHeaderAndReplacePreviousPort(): void {
    $r = new Request('http://foo.com:8124/bar');
    $r = $r->withUri(new Uri('http://foo.com:8125/bar'));
    expect($r->getHeaderLine('host'))->toBeSame('foo.com:8125');
  }

  <<ExpectedException(\InvalidArgumentException::class), ExpectedExceptionMessage('Header name must be an RFC 7230 compatible string.')>>
  public function testCannotHaveHeaderWithEmptyName(): void {
    $r = new Request('https://example.com/');
    $r->withHeader('', 'Bar');
  }

  <<ExpectedException(\InvalidArgumentException::class)>>
  public function testCanHaveHeaderWithEmptyValue(): void {
    $r = new Request('https://example.com/');
    $r = $r->withHeader('Foo', '');
    expect($r->getHeader('Foo'))->toBeSame(['']);
  }
}
