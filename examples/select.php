<pre>
<?php
require '../vendor/autoload.php';

use QueryBuilder\QueryBuilder as QB;

QB::setAdapter(new QueryBuilder\MySqlAdapter());

$result = QB::select(['user', 'u'])
  ->columns(['u.id', 'uname' => 'username'])
  ->join(['user_permission', 'p'], QB::raw('p.user_id = u.id'))
  ->limit(5, 0)
  ->groupby(['u.id'])
  ->orderby(['username ASC', 'firstname ASC'])
  ->where('firstname', '=', 'Christoffer')
  ->whereNot('u.id', 'IN', array(2))
  ->toSql();

var_dump($result['sql'], $result['params']);

$result = QB::select('user')
  ->orderby('id')
  ->where(function($qb) {
    $qb->where('name', 'LIKE', '%chris%')
      ->whereOr('username', 'LIKE', '%chris%');
  })
  ->where('active', '=', 1)
  ->toSql();

var_dump($result['sql'], $result['params']);
?>
</pre>