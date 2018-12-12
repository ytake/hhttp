<?hh // strict

require __DIR__ . '/../vendor/hh_autoload.php';

use namespace HH\Lib\Experimental\IO;

<<__Entrypoint>>
function main(): void {
  $i = IO\request_input();
  var_dump($i->rawReadBlocking());
  // var_dump($i->rawReadBlocking());
}
