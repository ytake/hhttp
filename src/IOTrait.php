<?hh // strict

namespace Ytake\Hungrr;

use namespace HH\Lib\Experimental\IO;

trait IOTrait {

  private ?IO\ReadHandle $readHandle;
  private ?IO\WriteHandle $writeHandle;

  protected function createIO(): void {
    list($this->readHandle, $this->writeHandle) = IO\pipe_non_disposable();
  }

  public function setBody(string $body): void {
    $wh = $this->writeHandle;
    invariant($wh is IO\WriteHandle, "handle error.");
    $wh->rawWriteBlocking($body);
  }
}
