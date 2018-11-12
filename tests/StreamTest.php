<?hh // strict

use type Ytake\Hhttp\Stream;
use type Facebook\HackTest\HackTest;

use function Facebook\FBExpect\expect;

final class StreamTest extends HackTest {

  public function testConstructorInitializesProperties(): void {
    $handle = fopen('php://temp', 'r+');
    fwrite($handle, 'data');
    $stream = new Stream($handle);
    expect($stream->isReadable())->toBeTrue();
    expect($stream->isWritable())->toBeTrue();
    expect($stream->isSeekable())->toBeTrue();
    expect($stream->getMetadata('uri'))->toBeSame('php://temp');
    expect($stream->getMetadata())->toBeType('array');
    expect($stream->getSize())->toBeSame(4);
    expect($stream->eof())->toBeFalse();
    fwrite($handle, 'data');
    $stream->close();
  }

  public function testShouldCloseStreamHandler(): void {
    $handle = fopen('php://temp', 'r');
    $stream = new Stream($handle);
    $stream->close();
    $stream = null;
    expect(is_resource($handle))->toBeFalse();
  }

  public function testConvertsToString(): void {
    $handle = fopen('php://temp', 'w+');
    fwrite($handle, 'data');
    $stream = new Stream($handle);
    expect((string) $stream)->toBeSame('data');
    expect((string) $stream)->toBeSame('data');
    $stream->close();
  }

  public function testGetsContents(): void {
    $handle = fopen('php://temp', 'w+');
    fwrite($handle, 'data');
    $stream = new Stream($handle);
    expect($stream->getContents())->toBeEmpty();
    $stream->seek(0);
    expect($stream->getContents())->toBeSame('data');
    expect($stream->getContents())->toBeEmpty();
  }

  public function testChecksEof(): void {
    $handle = fopen('php://temp', 'w+');
    fwrite($handle, 'data');
    $stream = new Stream($handle);
    expect($stream->eof())->toBeFalse();
    $stream->read(4);
    expect($stream->eof())->toBeTrue();
    $stream->close();
  }

  public function testGetSize(): void{
    $size = filesize(__FILE__);
    $handle = fopen(__FILE__, 'r');
    $stream = new Stream($handle);
    expect($stream->getSize())->toBeSame($size);
    expect($stream->getSize())->toBeSame($size);
    $stream->close();
  }

  public function testEnsuresSizeIsConsistent(): void {
    $h = fopen('php://temp', 'w+');
    expect(fwrite($h, 'foo'))->toBeSame(3);

    $stream = new Stream($h);
    expect($stream->getSize())->toBeSame(3);
    expect($stream->write('test'));
    expect($stream->getSize())->toBeSame(7);
    expect($stream->getSize())->toBeSame(7);
    $stream->close();
  }

  public function testProvidesStreamPosition(): void {
    $handle = fopen('php://temp', 'w+');
    $stream = new Stream($handle);
    expect($stream->tell())->toBeSame(0);
    $stream->write('foo');
    expect($stream->tell())->toBeSame(3);
    $stream->seek(1);
    expect($stream->tell())->toBeSame(1);
    expect($stream->tell())->toBeSame(ftell($handle));
    $stream->close();
  }

  public function testCanDetachStream(): void {
    $r = fopen('php://temp', 'w+');
    $stream = new Stream($r);
    $stream->write('foo');
    expect($stream->isReadable())->toBeTrue();
    expect($stream->detach())->toBeSame($r);
    $stream->detach();

    expect($stream->isReadable())->toBeFalse();
    expect($stream->isWritable())->toBeFalse();
    expect($stream->isSeekable())->toBeFalse();

    $throws = ($fn) ==> {
      try {
        $fn($stream);
        self::fail();
      } catch (\Exception $e) {}
    };
    $throws(function ($stream) {
      $stream->read(10);
    });
    $throws(function ($stream) {
      $stream->write('bar');
    });
        $throws(function ($stream) {
            $stream->seek(10);
        });
        $throws(function ($stream) {
            $stream->tell();
        });
        $throws(function ($stream) {
            $stream->eof();
        });
        $throws(function ($stream) {
            $stream->getSize();
        });
        $throws(function ($stream) {
            $stream->getContents();
        });
    expect((string) $stream)->toBeEmpty();
    $stream->close();
  }

  public function testCloseClearProperties():void {
    $handle = fopen('php://temp', 'r+');
    $stream = new Stream($handle);
    $stream->close();
    expect($stream->isSeekable())->toBeFalse();
    expect($stream->isReadable())->toBeFalse();
    expect($stream->isWritable())->toBeFalse();
    expect($stream->getSize())->toBeNull();
    expect($stream->getMetadata())->toBeEmpty();
  }
}
