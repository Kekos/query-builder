<?php
/**
 * QueryBuilder for PHP
 *
 * JoinBuilder class
 *
 * @version 1.1
 * @date 2017-12-31
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;

class JoinBuilder extends CriteriaBuilder {

  protected $table;
  protected $join_type;

  public function __construct($adapter, $statements, $table, $join_type) {
    parent::__construct($adapter, $statements);

    $this->table = $table;
    $this->join_type = $join_type;
  }

  public function on($key, $operator = null, $value = null, $joiner = 'AND') {
    $this->where($key, $operator, $value, $joiner);
    return $this;
  }

  public function onOr($key, $operator = null, $value = null) {
    $this->where($key, $operator, $value, 'OR');
    return $this;
  }

  public function toSql() {
    extract(parent::toSql());

    if (is_array($this->table)) {
      list($table, $alias) = $this->table;
      $table = $this->sanitizeField($table) . " AS " . $this->sanitizeField($alias);

    } else if ($this->table instanceof Raw) {
      $table = (string) $this->table;
      $params = array_merge($params, $this->table->getParams());

    } else {
      $table = $this->sanitizeField($this->table);
    }

    $sql = $this->join_type . " JOIN " . $table . " ON " . $sql;

    return compact('sql', 'params');
  }
}
?>