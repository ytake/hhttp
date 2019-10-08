use type Ytake\Hungrr\{Response, StatusCode};
use type Facebook\HackTest\{DataProvider, HackTest};

use namespace HH\Lib\Experimental\IO;
use function Facebook\FBExpect\expect;

final class ResponseTest extends HackTest {

  public async function testDefaultConstructor(): Awaitable<void> {
    list($read, $write) = IO\pipe_non_disposable();
    $r = new Response($write);
    expect($r->getStatusCode())->toBeSame(200);
    expect($r->getProtocolVersion())->toBeSame('1.1');
    expect($r->getReasonPhrase())->toBeSame('OK');
    expect($r->getHeaders())->toBeSame(dict[]);
    await $r->getBody()->closeAsync();
    $re = await $read->readAsync();
    expect($re)->toBeSame('');
  }

  public function testCanConstructWithStatusCode(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::NOT_FOUND);
    expect($r->getStatusCode())->toBeSame(404);
    expect($r->getReasonPhrase())->toBeSame('Not Found');
  }

  public function testStatusCanBeNumericString(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::NOT_FOUND);
    $r2 = $r->withStatus(StatusCode::CREATED);
    expect($r->getStatusCode())->toBeSame(404);
    expect($r->getReasonPhrase())->toBeSame('Not Found');
    expect($r2->getStatusCode())->toBeSame(201);
    expect($r2->getReasonPhrase())->toBeSame('Created');
  }

  public function testCanConstructWithHeaders(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar']]);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r->getHeaderLine('Foo'))->toBeSame('Bar');
    expect($r->getHeader('Foo'))->toBeSame(vec['Bar']);
  }

  public async function testCanConstructWithBody(): Awaitable<void> {
    list($read, $write) = IO\pipe_non_disposable();
    await $write->writeAsync('baz');
    $r = new Response($write, StatusCode::OK, dict[]);
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    await $r->getBody()->closeAsync();
    $re = await $read->readAsync();
    expect($re)->toBeSame('baz');
  }

  public async function testNullBody(): Awaitable<void> {
    list($read, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict[]);
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    await $r->getBody()->closeAsync();
    $re = await $read->readAsync();
    expect($re)->toBeSame('');
  }

  public async function testFalseyBody(): Awaitable<void> {
    list($read, $write) = IO\pipe_non_disposable();
    await $write->writeAsync('0');
    $r = new Response($write, StatusCode::OK, dict[]);
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    await $r->getBody()->closeAsync();
    $re = await $read->readAsync();
    expect($re)->toBeSame('0');
  }

  public function testCanConstructWithReason(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict[], '1.1', 'bar');
    expect($r->getReasonPhrase())->toBeSame('bar');
    $r = new Response($write, StatusCode::OK, dict[], '1.1', '0');
    expect($r->getReasonPhrase())->toBeSame('0');
  }

  public function testCanConstructWithProtocolVersion(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict[], '1000');
    expect($r->getProtocolVersion())->toBeSame('1000');
  }

  public function testWithStatusCodeAndNoReason(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = (new Response($write,))->withStatus(201);
    expect($r->getStatusCode())->toBeSame(201);
    expect($r->getReasonPhrase())->toBeSame('Created');
  }

  public function testWithStatusCodeAndReason(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = (new Response($write,))->withStatus(201, 'Foo');
    expect($r->getStatusCode())->toBeSame(201);
    expect($r->getReasonPhrase())->toBeSame('Foo');
    $r = (new Response(IO\request_output(),))->withStatus(201, '0');
    expect($r->getStatusCode())->toBeSame(201);
    expect($r->getReasonPhrase())->toBeSame('0');
  }

  public function testWithProtocolVersion(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = (new Response($write,))->withProtocolVersion('1000');
    expect($r->getProtocolVersion())->toBeSame('1000');
  }

  public function testSameInstanceWhenSameProtocol(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write,);
    expect($r->withProtocolVersion('1.1'))->toBeSame($r);
  }

  public async function testWithBody(): Awaitable<void> {
    list($read, $write) = IO\pipe_non_disposable();
    await $write->writeAsync('testing');
    $r = new Response($write);
    expect($r->getBody())->toBeInstanceOf(IO\ReadHandle::class);
    await $r->getBody()->closeAsync();
    $re = await $read->readAsync();
    expect($re)->toBeSame('testing');
  }

  public function testWithHeader(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar']]);
    $r2 = $r->withHeader('baZ', vec['Bam']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'baZ' => vec['Bam']]);
    expect($r2->getHeaderLine('baz'))->toBeSame('Bam');
    expect($r2->getHeader('baz'))->toBeSame(vec['Bam']);
  }

  public function testWithHeaderAsArray(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar']]);
    $r2 = $r->withHeader('baZ', vec['Bam', 'Bar']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'baZ' => vec['Bam', 'Bar']]);
    expect($r2->getHeaderLine('baz'))->toBeSame('Bam, Bar');
    expect($r2->getHeader('baz'))->toBeSame(vec['Bam', 'Bar']);
  }

  public function testWithHeaderReplacesDifferentCase(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar']]);
    $r2 = $r->withHeader('foO', vec['Bam']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['foO' => vec['Bam']]);
    expect($r2->getHeaderLine('foo'))->toBeSame('Bam');
    expect($r2->getHeader('foo'))->toBeSame(vec['Bam']);
  }

  public function testWithAddedHeader(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar']]);
    $r2 = $r->withAddedHeader('foO', vec['Baz']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar', 'Baz']]);
    expect($r2->getHeaderLine('foo'))->toBeSame('Bar, Baz');
    expect($r2->getHeader('foo'))->toBeSame(vec['Bar', 'Baz']);
  }

  public function testWithAddedHeaderAsArray(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar']]);
    $r2 = $r->withAddedHeader('foO', vec['Baz', 'Bam']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar', 'Baz', 'Bam']]);
    expect($r2->getHeaderLine('foo'))->toBeSame('Bar, Baz, Bam');
    expect($r2->getHeader('foo'))->toBeSame(vec['Bar', 'Baz', 'Bam']);
  }

  public function testWithAddedHeaderThatDoesNotExist(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar']]);
    $r2 = $r->withAddedHeader('nEw', vec['Baz']);
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar']]);
    expect($r2->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'nEw' => vec['Baz']]);
    expect($r2->getHeaderLine('new'))->toBeSame('Baz');
    expect($r2->getHeader('new'))->toBeSame(vec['Baz']);
  }

  public function testWithoutHeaderThatExists(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Foo' => vec['Bar'], 'Baz' => vec['Bam']]);
    $r2 = $r->withoutHeader('foO');
    expect($r->hasHeader('foo'))->toBeTrue();
    expect($r->getHeaders())->toBeSame(dict['Foo' => vec['Bar'], 'Baz' => vec['Bam']]);
    expect($r2->hasHeader('foo'))->toBeFalse();
    expect($r2->getHeaders())->toBeSame(dict['Baz' => vec['Bam']]);
  }

  public function testWithoutHeaderThatDoesNotExist(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write, StatusCode::OK, dict['Baz' => vec['Bam']]);
    $r2 = $r->withoutHeader('foO');
    expect($r2)->toBeSame($r);
    expect($r2->hasHeader('foo'))->toBeFalse();
    expect($r2->getHeaders())->toBeSame(dict['Baz' => vec['Bam']]);
  }

  public function testSameInstanceWhenRemovingMissingHeader(): void {
    list($_, $write) = IO\pipe_non_disposable();
    $r = new Response($write,);
    expect($r->withoutHeader('foo'))->toBeSame($r);
  }

  public function trimmedHeaderValues(): vec<(Response)> {
    list($_, $write) = IO\pipe_non_disposable();
    return vec[
      tuple(new Response($write, StatusCode::OK, dict['OWS' => vec[" \t \tFoo\t \t "]])),
      tuple((new Response($write))->withHeader('OWS', vec[" \t \tFoo\t \t "])),
      tuple((new Response($write,))->withAddedHeader('OWS', vec[" \t \tFoo\t \t "])),
    ];
  }

  <<DataProvider('trimmedHeaderValues')>>
  public function testHeaderValuesAreTrimmed(Response $r): void {
    expect($r->getHeaders())->toBeSame(dict['OWS' => vec['Foo']]);
    expect($r->getHeaderLine('OWS'))->toBeSame('Foo');
    expect($r->getHeader('OWS'))->toBeSame(vec['Foo']);
  }
}
