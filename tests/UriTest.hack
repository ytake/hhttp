use type Ytake\Hungrr\Uri;
use type Facebook\HackTest\{DataProvider, HackTest};

use function Facebook\FBExpect\expect;

final class UriTest extends HackTest {

  const string RFC3986_BASE = 'http://a/b/c/d;p?q';

  public function testItShouldParseProvidedUri(): void {
    $uri = new Uri('https://user:pass@example.com:8080/path/123?q=abc#test');
    expect($uri->getScheme())->toBeSame('https');
    expect($uri->getAuthority())->toBeSame('user:pass@example.com:8080');
    expect($uri->getUserInfo())->toBeSame(shape('user' => 'user', 'pass' => 'pass'));
    expect($uri->getHost())->toBeSame('example.com');
    expect($uri->getPort())->toBeSame(8080);
    expect($uri->getPath())->toBeSame('/path/123');
    expect($uri->getRawQuery())->toBeSame('q=abc');
    expect($uri->getFragment())->toBeSame('test');
    expect($uri->toString())->toBeSame('https://user:pass@example.com:8080/path/123?q=abc#test');
  }

  public function testItCanTransformAndRetrievePartsIndividually(): void {
    $uri = (new Uri())
      ->withScheme('https')
      ->withUserInfo('user', 'pass')
      ->withHost('example.com')
      ->withPort(8080)
      ->withPath('/path/123')
      ->withRawQuery('q=abc')
      ->withFragment('test');
    expect($uri->getScheme())->toBeSame('https');
    expect($uri->getAuthority())->toBeSame('user:pass@example.com:8080');
    expect($uri->getUserInfo())->toBeSame(shape('user' => 'user', 'pass' => 'pass'));
    expect($uri->getHost())->toBeSame('example.com');
    expect($uri->getPort())->toBeSame(8080);
    expect($uri->getPath())->toBeSame('/path/123');
    expect($uri->getRawQuery())->toBeSame('q=abc');
    expect($uri->getFragment())->toBeSame('test');
    expect($uri->toString())->toBeSame('https://user:pass@example.com:8080/path/123?q=abc#test');
  }

  public function vecUris(): vec<(string)> {
    return vec[
      tuple('urn:path-rootless'),
      tuple('urn:path:with:colon'),
      tuple('urn:/path-absolute'),
      tuple('urn:/'),
      tuple('urn:'),
      tuple('/'),
      tuple('relative/'),
      tuple('0'),
      tuple(''),
      tuple('//example.org'),
      tuple('//example.org/'),
      tuple('//example.org?q=#h'),
      tuple('?q='),
      tuple('?q=abc&foo=bar'),
      tuple('#fragment'),
      tuple('./foo/../bar'),
    ];
  }

  <<DataProvider('vecUris')>>
  public function testValidUrisStayValid(string $input): void {
    expect(new Uri($input) |> $$->toString())->toBeSame($input);
  }
}
