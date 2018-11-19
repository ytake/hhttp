<?hh // strict

use type Ytake\Hhttp\Response;
use type Ytake\Hhttp\StatusCode;
use type Facebook\HackTest\HackTest;

use namespace HH\Lib\Experimental\IO;
use function Facebook\FBExpect\expect;

final class ResponseTest extends HackTest {

  public function testDefaultConstructor(): void {
    $r = new Response();
    expect($r->getStatusCode())->toBeSame(200);
    expect($r->getProtocolVersion())->toBeSame('1.1');
    expect($r->getReasonPhrase())->toBeSame('OK');
    expect($r->getHeaders())->toBeSame(dict[]);
    expect($r->readBody()->rawReadBlocking())->toBeSame('');
  }

  public function testCanConstructWithStatusCode(): void {
    $r = new Response(StatusCode::NotFound);
    expect($r->getStatusCode())->toBeSame(404);
    expect($r->getReasonPhrase())->toBeSame('Not Found');
  }

  public function testStatusCanBeNumericString(): void {
    $r = new Response(StatusCode::NotFound);
    $r2 = $r->withStatus(StatusCode::Created);
    expect($r->getStatusCode())->toBeSame(404);
    expect($r->getReasonPhrase())->toBeSame('Not Found');
    expect($r2->getStatusCode())->toBeSame(201);
    expect($r2->getReasonPhrase())->toBeSame('Created');
  }

  public function testCanConstructWithHeaders(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar']});
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r->getHeaderLine('Foo'))->toBeSame('Bar');
    expect($r->getHeader('Foo'))->toBeSame(vec['Bar']);
  }

  public function testCanConstructWithBody(): void {
    $r = new Response(StatusCode::Ok, Map{}, 'baz');
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    expect($r->readBody()->rawReadBlocking())->toBeSame('baz');
  }

  public function testNullBody(): void {
    $r = new Response(StatusCode::Ok, Map{});
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    expect($r->readBody()->rawReadBlocking())->toBeSame('');
  }

  public function testFalseyBody(): void {
    $r = new Response(StatusCode::Ok, Map{}, '0');
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    expect($r->readBody()->rawReadBlocking())->toBeSame('0');
  }

  public function testCanConstructWithReason(): void {
    $r = new Response(StatusCode::Ok, Map{}, '', '1.1', 'bar');
    expect($r->getReasonPhrase())->toBeSame('bar');
    $r = new Response(StatusCode::Ok, Map{}, '', '1.1', '0');
    expect($r->getReasonPhrase())->toBeSame('0');
  }

  public function testCanConstructWithProtocolVersion(): void {
    $r = new Response(StatusCode::Ok, Map{}, '', '1000');
    expect($r->getProtocolVersion())->toBeSame('1000');
  }

  public function testWithStatusCodeAndNoReason(): void {
    $r = (new Response())->withStatus(201);
    expect($r->getStatusCode())->toBeSame(201);
    expect($r->getReasonPhrase())->toBeSame('Created');
  }

  public function testWithStatusCodeAndReason(): void {
    $r = (new Response())->withStatus(201, 'Foo');
    expect($r->getStatusCode())->toBeSame(201);
    expect($r->getReasonPhrase())->toBeSame('Foo');
    $r = (new Response())->withStatus(201, '0');
    expect($r->getStatusCode())->toBeSame(201);
    expect($r->getReasonPhrase())->toBeSame('0');
  }

  public function testWithProtocolVersion(): void {
    $r = (new Response())->withProtocolVersion('1000');
    expect($r->getProtocolVersion())->toBeSame('1000');
  }

  public function testSameInstanceWhenSameProtocol(): void {
    $r = new Response();
    expect($r->withProtocolVersion('1.1'))->toBeSame($r);
  }

  public function testWithBody(): void {
    $r = new Response();
    $r->setBody('testing');
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    expect($r->readBody()->rawReadBlocking())->toBeSame('testing');
  }

  public function testWithHeader(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar']});
    $r2 = $r->withHeader('baZ', vec['Bam']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'baZ' => vec['Bam']]);
    expect($r2->getHeaderLine('baz'))->toBeSame('Bam');
    expect($r2->getHeader('baz'))->toBeSame(vec['Bam']);
  }

  public function testWithHeaderAsArray(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar']});
    $r2 = $r->withHeader('baZ', vec['Bam', 'Bar']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'baZ' => vec['Bam', 'Bar']]);
    expect($r2->getHeaderLine('baz'))->toBeSame('Bam, Bar');
    expect($r2->getHeader('baz'))->toBeSame(vec['Bam', 'Bar']);
  }

  public function testWithHeaderReplacesDifferentCase(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar']});
    $r2 = $r->withHeader('foO', vec['Bam']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['foO' => vec['Bam']]);
    expect($r2->getHeaderLine('foo'))->toBeSame('Bam');
    expect($r2->getHeader('foo'))->toBeSame(vec['Bam']);
  }

  public function testWithAddedHeader(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar']});
    $r2 = $r->withAddedHeader('foO', vec['Baz']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar', 'Baz']]);
    expect($r2->getHeaderLine('foo'))->toBeSame('Bar, Baz');
    expect($r2->getHeader('foo'))->toBeSame(vec['Bar', 'Baz']);
  }

  public function testWithAddedHeaderAsArray(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar']});
    $r2 = $r->withAddedHeader('foO', vec['Baz', 'Bam']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar', 'Baz', 'Bam']]);
    expect($r2->getHeaderLine('foo'))->toBeSame('Bar, Baz, Bam');
    expect($r2->getHeader('foo'))->toBeSame(vec['Bar', 'Baz', 'Bam']);
  }

  public function testWithAddedHeaderThatDoesNotExist(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar']});
    $r2 = $r->withAddedHeader('nEw', vec['Baz']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'nEw' => vec['Baz']]);
    expect($r2->getHeaderLine('new'))->toBeSame('Baz');
    expect($r2->getHeader('new'))->toBeSame(vec['Baz']);
  }

  public function testWithoutHeaderThatExists(): void {
    $r = new Response(StatusCode::Ok, Map{'Foo' => vec['Bar'], 'Baz' => vec['Bam']});
    $r2 = $r->withoutHeader('foO');
    expect($r->hasHeader('foo'))->toBeTrue();
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'Baz' => vec['Bam']]);
    expect($r2->hasHeader('foo'))->toBeFalse();
    expect($r2->getHeaders())->toBeSame(dict['Baz' => vec['Bam']]);
  }

  public function testWithoutHeaderThatDoesNotExist(): void {
    $r = new Response(StatusCode::Ok, Map{'Baz' => vec['Bam']});
    $r2 = $r->withoutHeader('foO');
    expect($r2)->toBeSame($r);
    expect($r2->hasHeader('foo'))->toBeFalse();
    expect($r2->getHeaders())->toBeSame(dict['Baz' => vec['Bam']]);
  }

  public function testSameInstanceWhenRemovingMissingHeader(): void {
    $r = new Response();
    expect($r->withoutHeader('foo'))->toBeSame($r);
  }

  public function trimmedHeaderValues(): vec<(Response)> {
    return vec[
      tuple(new Response(StatusCode::Ok, Map{'OWS' => vec[" \t \tFoo\t \t "]})),
      tuple((new Response())->withHeader('OWS', vec[" \t \tFoo\t \t "])),
      tuple((new Response())->withAddedHeader('OWS', vec[" \t \tFoo\t \t "])),
    ];
  }

  <<DataProvider('trimmedHeaderValues')>>
  public function testHeaderValuesAreTrimmed(Response $r): void {
    expect($r->getHeaders())->toBeSame(dict['OWS' => vec['Foo']]);
    expect($r->getHeaderLine('OWS'))->toBeSame('Foo');
    expect($r->getHeader('OWS'))->toBeSame(vec['Foo']);
  }
}
