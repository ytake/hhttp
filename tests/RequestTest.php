<?hh // strict

use type Ytake\Hungrr\Uri;
use type Ytake\Hungrr\Request;
use type Facebook\HackTest\HackTest;

use namespace HH\Lib\Experimental\IO;
use namespace Facebook\Experimental\Http\Message;
use function Facebook\FBExpect\expect;

final class RequestTest extends HackTest {

  public function testRequestUriMayBeString(): void {
    $r = new Request(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    expect((string) $r->getUri())->toBeSame('/');
  }

  public function testRequestUriMayBeUri(): void {
    $uri = new Uri('/');
    $r = new Request(Message\HTTPMethod::GET, $uri, IO\request_input());
    expect($r->getUri())->toBeSame($uri);
  }

  <<ExpectedException(\InvalidArgumentException::class), ExpectedExceptionMessage('Unable to parse URI: ///')>>
  public function testValidateRequestUri(): void {
    new Request(Message\HTTPMethod::GET, new Uri('///'), IO\request_input());
  }

  public function testCanConstructWithBody(): void {
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('baz');
    $re = new Request(Message\HTTPMethod::GET, new Uri('/'), $r);
    expect($re->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    expect($re->getBody()->rawReadBlocking())->toBeSame('baz');
  }

  public function testNullBody(): void{
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('');
    $re = new Request(Message\HTTPMethod::GET, new Uri('/'), $r);
    expect($re->getBody()->rawReadBlocking())->toBeSame('');
  }

  public function testFalseyBody(): void {
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('0');
    $re = new Request(Message\HTTPMethod::GET, new Uri('/'), $r);
    expect($re->getBody()->rawReadBlocking())->toBeSame('0');
  }

  public function testWithUri(): void {
    $r1 = new Request(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    $u1 = $r1->getUri();
    $u2 = new Uri('http://www.example.com');
    $r2 = $r1->withUri($u2);
    expect($r2)->toNotBeSame($r1);
    expect($r2->getUri())->toBeSame($u2);
    expect($r1->getUri())->toBeSame($u1);
  }

  public function testSameInstanceWhenSameUri(): void {
    $r1 = new Request(Message\HTTPMethod::GET, new Uri('http://foo.com'), IO\request_input());
    $r2 = $r1->withUri($r1->getUri());
    expect($r2)->toBeSame($r1);
  }

  public function testWithRequestTarget(): void {
    $r1 = new Request(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    $r2 = $r1->withRequestTarget('*');
    expect($r2->getRequestTarget())->toBeSame('*');
    expect($r1->getRequestTarget())->toBeSame('/');
  }

  <<ExpectedException(\InvalidArgumentException::class), ExpectedExceptionMessage('Invalid request target provided; cannot contain whitespace')>>
  public function testRequestTargetDoesNotAllowSpaces(): void {
    $r1 = new Request(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    $r1->withRequestTarget('/foo bar');
  }

  public function testRequestTargetDefaultsToSlash(): void {
    $r1 = new Request(Message\HTTPMethod::GET, new Uri(''), IO\request_input());
    expect($r1->getRequestTarget())->toBeSame('/');
    $r2 = new Request(Message\HTTPMethod::GET, new Uri('*'), IO\request_input());
    expect($r2->getRequestTarget())->toBeSame('*');
    $r3 = new Request(Message\HTTPMethod::GET, new Uri('http://foo.com/bar baz/'), IO\request_input());
    expect($r3->getRequestTarget())->toBeSame('/bar%20baz/');
  }

  public function testBuildsRequestTarget(): void {
    $r1 = new Request(Message\HTTPMethod::GET, new Uri('http://foo.com/baz?bar=bam'), IO\request_input());
    expect($r1->getRequestTarget())->toBeSame('/baz?bar=bam');
  }

  public function testBuildsRequestTargetWithFalseyQuery(): void {
    $r1 = new Request(Message\HTTPMethod::GET, new Uri('http://foo.com/baz?0'), IO\request_input());
    expect($r1->getRequestTarget())->toBeSame('/baz?0');
  }

  public function testHostIsAddedFirst(): void {
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('testing');
    $re = new Request(
      Message\HTTPMethod::GET,
      new Uri('http://foo.com/baz?bar=bam'),
      $r,
      dict['Foo' => vec['Bar']],
    );
    expect($re->getHeaders())->toContainKey('Host');
    expect($re->getHeaders())->toContainKey('Foo');
  }

  public function testCanGetHeaderAsCsv(): void {
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('testing');
    $re = new Request(
      Message\HTTPMethod::GET,
      new Uri('http://foo.com/baz?bar=bam'),
      $r,
      dict[
        'Foo' => vec['a', 'b', 'c'],
      ],
    );
    expect($re->getHeaderLine('Foo'))->toBeSame('a, b, c');
    expect($re->getHeaderLine('Bar'))->toBeSame('');
  }

  public function testHostIsNotOverwrittenWhenPreservingHost(): void {
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('testing');
    $re = new Request(
      Message\HTTPMethod::GET, 
      new Uri('http://foo.com/baz?bar=bam'),
      $r,
      dict['Host' => vec['a.com']]
    );
    expect($re->getHeaders())->toBeSame(dict['Host' => vec['a.com']]);
    $r2 = $re->withUri(new Uri('http://www.foo.com/bar'), shape('preserveHost' => true));
    expect($r2->getHeaderLine('Host'))->toBeSame('a.com');
  }

  public function testOverridesHostWithUri(): void {
    $r = new Request(
      Message\HTTPMethod::GET,
      new Uri('http://foo.com/baz?bar=bam'),
      IO\request_input()
    );
    expect($r->getHeaders())->toBeSame(dict['Host' => vec['foo.com']]);
    $r2 = $r->withUri(new Uri('http://www.baz.com/bar'));
    expect($r2->getHeaderLine('Host'))->toBeSame('www.baz.com');
  }

  public function testAggregatesHeaders(): void {
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('testing');
    $re = new Request(
      Message\HTTPMethod::GET, 
      new Uri(''),
      $r,
      dict[
      'ZOO' => vec['zoobar'],
      'zoo' => vec['foobar', 'zoobar'],
    ]);
    expect($re->getHeaders())->toBeSame(dict['ZOO' => vec['zoobar', 'foobar', 'zoobar']]);
    expect($re->getHeaderLine('zoo'))->toBeSame('zoobar, foobar, zoobar');
  }

  public function testSupportNumericHeaders(): void {
    list($r, $w) = IO\pipe_non_disposable();
    $w->rawWriteBlocking('testing');
    $re = new Request(
      Message\HTTPMethod::GET, 
      new Uri(''),
      $r,
      dict[
      'Content-Length' => vec['200'],
    ]);
    expect($re->getHeaders())->toBeSame(dict['Content-Length' => vec['200']]);
    expect($re->getHeaderLine('Content-Length'))->toBeSame('200');
  }

  public function testAddsPortToHeader(): void {
    $r = new Request(
      Message\HTTPMethod::GET, 
      new Uri('http://foo.com:8124/bar'),
      IO\request_input(),
    );
    expect($r->getHeaderLine('host'))->toBeSame('foo.com:8124');
  }

  public function testAddsPortToHeaderAndReplacePreviousPort(): void {
    $r = new Request(
      Message\HTTPMethod::GET, 
      new Uri('http://foo.com:8124/bar'),
      IO\request_input(),
    );
    $r = $r->withUri(new Uri('http://foo.com:8125/bar'));
    expect($r->getHeaderLine('host'))->toBeSame('foo.com:8125');
  }

  <<ExpectedException(\InvalidArgumentException::class), ExpectedExceptionMessage('Header name must be an RFC 7230 compatible string.')>>
  public function testCannotHaveHeaderWithEmptyName(): void {
    $r = new Request(
      Message\HTTPMethod::GET, 
      new Uri('https://example.com/'),
      IO\request_input(),
    );
    $r->withHeader('', vec['Bar']);
  }

  public function testCanHaveHeaderWithEmptyValue(): void {
    $r = new Request(
      Message\HTTPMethod::GET, 
      new Uri('https://example.com/'),
      IO\request_input(),
    );
    $r = $r->withHeader('Foo', vec['']);
    expect($r->getHeader('Foo'))->toBeSame(vec['']);
  }
}
