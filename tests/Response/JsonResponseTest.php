<?hh // strict

use type Ytake\Hungrr\Response\JsonResponse;
use type Ytake\Hungrr\StatusCode;
use type Facebook\HackTest\HackTest;
use function Facebook\FBExpect\expect;

final class JsonResponseTest extends HackTest {

  public function testShouldReturnEmptyJsonBody(): void {
    $r = new JsonResponse(new ImmMap([]));
    expect($r->getStatusCode())->toBeSame(200);
    expect($r->getProtocolVersion())->toBeSame('1.1');
    expect($r->getReasonPhrase())->toBeSame('OK');
    expect($r->getHeaders())->toBeSame(dict[
      'content-type' => vec['application/json'],
    ]);
    expect($r->readBody()->rawReadBlocking())->toBeSame('{}');
  }

  public function testShouldReturnJsonBody(): void {
    $r = new JsonResponse(new ImmMap([
      'testing' => ImmMap{
        'HHVM' => 'Hack'
      }
    ]));
    expect($r->getStatusCode())->toBeSame(200);
    expect($r->getProtocolVersion())->toBeSame('1.1');
    expect($r->getReasonPhrase())->toBeSame('OK');
    expect($r->getHeaders())->toBeSame(dict[
      'content-type' => vec['application/json'],
    ]);
    expect($r->readBody()->rawReadBlocking())->toBeSame('{"testing":{"HHVM":"Hack"}}');
  }

  public function testShouldReturnAppendHeaders(): void {
    $r = new JsonResponse(
      new ImmMap(['testing' => ImmMap{'HHVM' => 'Hack'}]),
      StatusCode::ACCEPTED,
      dict['X-App_Message' => vec['testing.'],
      'content-type' => vec['application/hal+json']]
    );
    expect($r->getStatusCode())->toBeSame(202);
    expect($r->getProtocolVersion())->toBeSame('1.1');
    expect($r->getReasonPhrase())->toBeSame('Accepted');
    expect($r->getHeaders())->toBeSame(dict[
      'X-App_Message' => vec['testing.'],
      'content-type' => vec['application/hal+json'],
    ]);
    expect($r->readBody()->rawReadBlocking())->toBeSame('{"testing":{"HHVM":"Hack"}}');
  }
}
