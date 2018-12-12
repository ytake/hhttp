<?hh // strict

use type Ytake\Hungrr\Uri;
use type Ytake\Hungrr\ServerRequest;
use type Facebook\HackTest\HackTest;
use namespace HH\Lib\Experimental\IO;
use namespace Facebook\Experimental\Http\Message;
use function Facebook\FBExpect\expect;

final class ServerRequestTest extends HackTest {

  public function testShouldBeSameServerParams(): void {
    $params = dict['name' => 'value'];
    $request = new ServerRequest(
      Message\HTTPMethod::GET,
      new Uri('/'),
      $r,
      dict[],
      '1.1',
      $params
    );
    expect($request->getServerParams())->toBeSame($params);
  }

  public function testShouldBeSameQueryParams(): void {
    $request = new ServerRequest('/', Message\HTTPMethod::GET);
    $params = dict['name' => 'value'];
    $request2 = $request->withQueryParams($params);
    expect($request)->toNotBeSame($request2);
    expect($request->getQueryParams())->toBeEmpty();
    expect($request2->getQueryParams())->toBeSame($params);
  }

  public function testShouldBeSameCookieParams(): void {
    $request = new ServerRequest('/');
    $params = dict['name' => 'value'];
    $request2 = $request->withCookieParams($params);
    expect($request)->toNotBeSame($request2);
    expect($request->getCookieParams())->toBeEmpty();
    expect($request2->getCookieParams())->toBeSame($params);
  }

  public function testShouldBeSameParsedBody(): void {
    $request = new ServerRequest('/');
    $params = dict['name' => 'value'];
    $request2 = $request->withParsedBody($params);
    expect($request)->toNotBeSame($request2);
    expect($request->getParsedBody())->toBeEmpty();
    expect($request2->getParsedBody())->toBeSame($params);
  }
}
