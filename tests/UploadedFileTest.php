<?hh // strict

use type Ytake\Hungrr\UploadedFile;
use type Facebook\HackTest\HackTest;
use namespace Ytake\Hungrr\Exception;
use namespace Facebook\Experimental\Http\Message;
use function Facebook\FBExpect\expect;

final class UploadedFileTest extends HackTest {

  private Vector<string> $files = Vector{};

  <<__Override>>
  public async function beforeEachTestAsync(): Awaitable<void> {
    $this->files = Vector{};
  }

  <<__Override>>
  public async function afterEachTestAsync(): Awaitable<void> {
    foreach($this->files as $file) {
      if (file_exists($file)) {
        unlink($file);
      }
    }
  }

  public function testShouldMovedFileExists(): void {
    $this->files[] = $from = tempnam(sys_get_temp_dir(), 'copy_from');
    $this->files[] = $to = tempnam(sys_get_temp_dir(), 'copy_to');
    copy(__FILE__, $from);
    $uf = new UploadedFile($from, null,basename($from), 'text/plain');
    $uf->moveTo($to);
    expect($tf = file_get_contents($to))->toNotBeEmpty();
    expect($ff = file_get_contents(__FILE__))->toNotBeEmpty();
    expect($tf)->toBeSame($ff);
  }

  <<ExpectedException(Exception\PathNotFoundException::class)>>
  public function testMoveCannotBeCalledMoreThanOnce(): void {
    $this->files[] = $from = tempnam(sys_get_temp_dir(), 'copy_from');
    $upload = new UploadedFile($from);
    $this->files[] = $to = tempnam(sys_get_temp_dir(), 'diac');
    $upload->moveTo($to);
    expect(file_exists($to))->toBeTrue();
    $upload->moveTo($to);
  }

  <<ExpectedException(Exception\UploadedFileException::class)>>
  public function testShouldThrow(): void {
    $this->files[] = $from = tempnam(sys_get_temp_dir(), 'copy_from');
    $upload = new UploadedFile($from, Message\UploadedFileError::ERROR_NO_FILE);
    $this->files[] = $to = tempnam(sys_get_temp_dir(), 'diac');
    $upload->moveTo($to);
  }
}
