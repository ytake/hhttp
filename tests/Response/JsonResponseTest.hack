use type Ytake\Hungrr\Response\JsonResponse;
use type Ytake\Hungrr\StatusCode;
use type Facebook\HackTest\HackTest;
use namespace HH\Lib\IO;
use function Facebook\FBExpect\expect;

final class JsonResponseTest extends HackTest {

  public async function testShouldReturnEmptyJsonBody(): Awaitable<void> {
    list($read, $write) = IO\pipe();
    $r = new JsonResponse($write);
    await $write->writeAsync(\json_encode(new ImmMap(dict[])));
    expect($r->getStatusCode())->toBeSame(200);
    expect($r->getProtocolVersion())->toBeSame('1.1');
    expect($r->getReasonPhrase())->toBeSame('OK');
    expect($r->getHeaders())->toBeSame(dict[
      'content-type' => vec['application/json'],
    ]);
    $handler = $r->getBody();
    if($handler is IO\CloseableHandle) {
      $handler->close();
    }
    $re = await $read->readAsync();
    expect($re)->toBeSame('{}');
  }

  public async function testShouldReturnJsonBody(): Awaitable<void> {
    list($read, $write) = IO\pipe();
    await $write->writeAsync(\json_encode(new ImmMap(dict[
      'testing' => ImmMap{
        'HHVM' => 'Hack'
      }
    ])));
    $r = new JsonResponse($write);
    expect($r->getStatusCode())->toBeSame(200);
    expect($r->getProtocolVersion())->toBeSame('1.1');
    expect($r->getReasonPhrase())->toBeSame('OK');
    expect($r->getHeaders())->toBeSame(dict[
      'content-type' => vec['application/json'],
    ]);
    $handler = $r->getBody();
    if($handler is IO\CloseableHandle) {
      $handler->close();
    }
    $re = await $read->readAsync();
    expect($re)->toBeSame('{"testing":{"HHVM":"Hack"}}');
  }

  public async function testShouldReturnAppendHeaders(): Awaitable<void> {
    list($read, $write) = IO\pipe();
    await $write->writeAsync(\json_encode(new ImmMap(dict['testing' => ImmMap{'HHVM' => 'Hack'}])));
    $r = new JsonResponse(
      $write,
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
    $handler = $r->getBody();
    if($handler is IO\CloseableHandle) {
      $handler->close();
    }
    $re = await $read->readAsync();
    expect($re)->toBeSame('{"testing":{"HHVM":"Hack"}}');
  }
}
