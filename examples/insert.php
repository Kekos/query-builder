<pre>
<?php
require '../vendor/autoload.php';

use QueryBuilder\QueryBuilder as QB;

QB::setAdapter(new QueryBuilder\MySqlAdapter());

$result = QB::insert('user')
    ->values([
        'username' => 'Kekos',
        'firstname' => 'Christoffer'
    ])
    ->toSql();

var_dump($result);
?>
</pre>