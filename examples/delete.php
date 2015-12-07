<pre>
<?php
require '../vendor/autoload.php';

use QueryBuilder\QueryBuilder as QB;

QB::setAdapter(new QueryBuilder\MySqlAdapter());

$result = QB::delete('user')
  ->where('id', '=', 2)
  ->toSql();

var_dump($result['sql'], $result['params']);
?>
</pre>