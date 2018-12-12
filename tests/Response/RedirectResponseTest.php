<?hh // strict

use type Ytake\Hungrr\Uri;
use type Ytake\Hungrr\Response\RedirectResponse;
use type Facebook\HackTest\HackTest;

use namespace Ytake\Hungrr\Exception;
use function Facebook\FBExpect\expect;

final class RedirectResponseTest extends HackTest {

  public function testShouldReturnRedirectHeaders(): void {
    $r = new RedirectResponse(new Uri('/foo/bar'));
    expect($r->getStatusCode())->toBeSame(302);
    expect($r->hasHeader('Location'))->toBeTrue();
    expect($r->getHeaderLine('Location'))->toBeSame('/foo/bar');
  }

  public function testShouldReturn302ResponseWithLocationHeader(): void {
    $uri = new Uri('https://example.com:10082/foo/bar');
    $r = new RedirectResponse($uri);
    expect($r->getStatusCode())->toBeSame(302);
    expect($r->hasHeader('Location'))->toBeTrue();
    expect($r->getHeaderLine('Location'))->toBeSame((string) $uri);
  }
}
