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
    $from = tempnam(sys_get_temp_dir(), 'copy_from');
    $this->files[] = $from;
    $to = tempnam(sys_get_temp_dir(), 'copy_to');
    $this->files[] = $to;
    copy(__FILE__, $from);
    $uf = new UploadedFile($from, null,basename($from), 'text/plain');
    $uf->moveTo($to);
    $tf = file_get_contents($to);
    $ff = file_get_contents(__FILE__);
    expect($tf)->toNotBeEmpty();
    expect($ff)->toNotBeEmpty();
    expect($tf)->toBeSame($ff);
  }

  public function testMoveCannotBeCalledMoreThanOnce(): void {
    $from = tempnam(sys_get_temp_dir(), 'copy_from');
    $this->files[] = $from;
    $upload = new UploadedFile($from);
    $to = tempnam(sys_get_temp_dir(), 'diac');
    $this->files[] = $to;
    $upload->moveTo($to);
    expect(file_exists($to))->toBeTrue();
    expect(() ==> $upload->moveTo($to))
      ->toThrow(Exception\PathNotFoundException::class);
  }

  public function testShouldThrow(): void {
    $from = tempnam(sys_get_temp_dir(), 'copy_from');
    $this->files[] = $from;
    $upload = new UploadedFile($from, Message\UploadedFileError::ERROR_NO_FILE);
    $to = tempnam(sys_get_temp_dir(), 'diac');
    $this->files[] = $to;
    expect(() ==> $upload->moveTo($to))
      ->toThrow(Exception\UploadedFileException::class);
  }
}
