<?hh

namespace Hack\UserDocumentation\API\Examples\AsyncMysql\RowBlock\GetIterator;

require __DIR__ .'/../../__includes/async_mysql_connect.inc.php';

use \Hack\UserDocumentation\API\Examples\AsyncMysql\ConnectionInfo as CI;

async function connect(\AsyncMysqlConnectionPool $pool):
  Awaitable<\AsyncMysqlConnection> {
  return await $pool->connect(
    CI::$host,
    CI::$port,
    CI::$db,
    CI::$user,
    CI::$passwd
  );
}
async function iterate(): Awaitable<int> {
  $pool = new \AsyncMysqlConnectionPool(array());
  $conn = await connect($pool);
  $result = await $conn->query(
    'SELECT * FROM test_table WHERE userID < 50'
  );
  $conn->close();
  // A call to $result->rowBlocks() actually pops the first element of the
  // row block Vector. So the call actually mutates the Vector.
  $row_blocks = $result->rowBlocks();
  if ($row_blocks->count() > 0) {
    // An AsyncMysqlRowBlock
    $row_block = $row_blocks[0];
    // An AsyncMysqlRowBlockIterator
    $rbit = $row_block->getIterator();
    // Iterating through a row block iterator will have an int key and
    // an AsyncMysqlRow as its value
    while ($rbit->valid()) {
      // current() gets you am AsyncMysqlRow, getIterator() gets you
      // an AsyncmysqlRowIterator
      $rit = $rbit->current()->getIterator();
      while ($rit->valid()) {
        // current() will give you a string value of the field in the row
        if ($rit->key() > 0 && is_numeric($rit->current())) {
          return intval($rit->current());
        }
        $rit->next();
      }
      $rbit->next();
    }
    return -1;
  } else {
    return -1;
  }
}

function run(): void {
  $r = \HH\Asio\join(iterate());
  var_dump($r);
}

run();
