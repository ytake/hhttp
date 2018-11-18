<?hh // strict

use type Ytake\Hhttp\Response;
use type Facebook\HackTest\HackTest;

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
}
