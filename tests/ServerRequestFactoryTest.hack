use type Ytake\Hungrr\ServerRequest;
use type Ytake\Hungrr\ServerRequestFactory;
use type Facebook\HackTest\HackTest;
use function Facebook\FBExpect\expect;

final class ServerRequestFactoryTest extends HackTest {

  public function testShouldBeServerRequestInstance(): void {
    $request = ServerRequestFactory::fromGlobals();
    expect($request)->toBeInstanceOf(ServerRequest::class);
    expect($request->getQueryParams())->toBeSame(dict[]);
    expect($request->getParsedBody())->toBeSame(dict[]);
    expect($request->getCookieParams())->toBeSame(dict[]);
    expect($request->getServerParams())->toNotBeSame(dict[]);
  }
}
