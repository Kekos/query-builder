# Query Builder for PHP

A fork of [Muhammad Usman's Pixie](https://github.com/usmanhalalit/pixie), a lightweight query builder for PHP.
This library only supports a subset of Pixie's fetures:

* Nested criterias (`WHERE`, `HAVING` and `ON` for joins)
* Raw queries

Query Builder is not able to open database connections or execute the built
queries.

## Install

You can install Query Builder via [Composer](http://getcomposer.org/):

```
composer require kekos/query-builder
```

## API

Start by configure Query Builder with an adapter:

```PHP
QueryBuilder::setAdapter(new MySqlAdapter());
```

The adapter sets the correct sanitizer character.
At this moment `MySqlAdapter` is provided with the library.

All builders have a `toSql()` method, which returns the SQL query and all
parameters to be bound to the query, to be used with prepared statements.

### Select

```PHP
$result = QueryBuilder::select(['user', 'u'])
  ->columns(['u.id', 'uname' => 'username'])
  ->join(['user_permission', 'p'], QB::raw('p.user_id = u.id'))
  ->limit(5, 0)
  ->groupby(['u.id'])
  ->orderby(['username ASC', 'firstname ASC'])
  ->where('firstname', '=', 'Christoffer')
  ->whereNot('u.id', 'IN', array(2))
  ->toSql();
```

`$result` will be an associative array with keys `sql` and `params`.

#### Nested `where` or `having`

```PHP
$result = QueryBuilder::select('user')
  ->orderby('id')
  ->where(function($qb) {
    $qb->where('name', 'LIKE', '%chris%')
      ->whereOr('username', 'LIKE', '%chris%');
  })
  ->where('active', '=', 1)
  ->toSql();
```

### Insert

```PHP
$result = QueryBuilder::insert('user')
  ->values([
      'username' => 'Kekos',
      'firstname' => 'Christoffer'
    ])
  ->toSql();
```

### Update

```PHP
$result = QueryBuilder::update('user')
  ->set([
      'username' => 'new_username',
      'firstname' => 'New firstname'
    ])
  ->where('id', '=', 2)
  ->toSql();
```

### Delete

```PHP
$result = QueryBuilder::delete('user')
  ->where('id', '=', 2)
  ->toSql();
```

## Bugs and improvements

Report bugs in GitHub issues or feel free to make a pull request :-)

## License

MIT