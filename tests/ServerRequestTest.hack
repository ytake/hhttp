use type Ytake\Hungrr\{ServerRequest, Uri};
use type Facebook\HackTest\HackTest;
use namespace HH\Lib\IO;
use namespace Facebook\Experimental\Http\Message;
use function Facebook\FBExpect\expect;

final class ServerRequestTest extends HackTest {

  public function testShouldBeSameServerParams(): void {
    $params = dict['name' => 'value'];
    list($r, $_w) = IO\pipe_nd();
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
    $request = new ServerRequest(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    $params = dict['name' => 'value'];
    $request2 = $request->withQueryParams($params);
    expect($request)->toNotBeSame($request2);
    expect($request->getQueryParams())->toBeEmpty();
    expect($request2->getQueryParams())->toBeSame($params);
  }

  public function testShouldBeSameCookieParams(): void {
    $request = new ServerRequest(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    $params = dict['name' => 'value'];
    $request2 = $request->withCookieParams($params);
    expect($request)->toNotBeSame($request2);
    expect($request->getCookieParams())->toBeEmpty();
    expect($request2->getCookieParams())->toBeSame($params);
  }

  public function testShouldBeSameParsedBody(): void {
    $request = new ServerRequest(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    $params = dict['name' => 'value'];
    $request2 = $request->withParsedBody($params);
    expect($request)->toNotBeSame($request2);
    expect($request->getParsedBody())->toBeEmpty();
    expect($request2->getParsedBody())->toBeSame($params);
  }

  public function testShouldExpectAttributes(): void {
    $request1 = new ServerRequest(Message\HTTPMethod::GET, new Uri('/'), IO\request_input());
    $request2 = $request1->withAttribute('name', 'value');
    $request3 = $request2->withAttribute('other', 'otherValue');
    $request4 = $request3->withoutAttribute('other');
    $request5 = $request3->withoutAttribute('unknown');
    expect($request1)->toNotBeSame($request2);
    expect($request2)->toNotBeSame($request3);
    expect($request3)->toNotBeSame($request4);
    expect($request4)->toNotBeSame($request5);
    expect($request1->getAttributes())->toBeSame(dict[]);
    expect($request1->getAttribute<string>('name'))->toBeNull();
    expect($request1->getAttribute<string>('name', 'something'))
      ->toBeSame('something');
    expect($request2->getAttribute<string>('name'))->toBeSame('value');
    expect($request2->getAttributes())
      ->toBeSame(dict['name' => 'value']);
    expect($request3->getAttributes())
      ->toBeSame(dict['name' => 'value', 'other' => 'otherValue']);
    expect($request4->getAttributes())->toBeSame(dict['name' => 'value']);
  }

  public function testShouldBeNull(): void {
    $request = (new ServerRequest(Message\HTTPMethod::GET, new Uri('/'), IO\request_input()))
      ->withAttribute('name', null);
    expect($request->getAttributes())->toBeSame(dict['name' => null]);
    expect($request->getAttribute('name', 'different-default'))->toBeNull();
    $requestWithoutAttribute = $request->withoutAttribute('name');
    expect($requestWithoutAttribute->getAttributes())->toBeSame(dict[]);
    expect($requestWithoutAttribute->getAttribute<string>('name', 'different-default'))
      ->toBeSame('different-default');
  }
}
