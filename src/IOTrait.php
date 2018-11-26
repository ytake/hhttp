<?hh // strict

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 *
 * Copyright (c) 2018 Yuuki Takezawa
 *
 */
 
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
