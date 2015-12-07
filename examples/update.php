<pre>
<?php
require '../vendor/autoload.php';

use QueryBuilder\QueryBuilder as QB;

QB::setAdapter(new QueryBuilder\MySqlAdapter());

$result = QB::update('user')
  ->set([
      'username' => 'new_username',
      'firstname' => 'New firstname'
    ])
  ->where('id', '=', 2)
  ->toSql();

var_dump($result['sql'], $result['params']);
?>
</pre>