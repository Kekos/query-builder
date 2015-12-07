<?php
/**
 * QueryBuilder for PHP
 *
 * Select class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;
use QueryBuilder\QueryBuilderException;

class Select extends CriteriaBase {

  private $columns = array();
  private $joins = array();
  private $group_by = array();
  private $having = array();
  private $order_by = array();
  private $limit_offset = null;
  private $limit_row_count = null;

  public function columns($columns) {
    if (!is_array($columns)) {
      $columns = array($columns);
    }

    foreach ($columns as $alias => &$column) {
      $column = $this->sanitizeField($column);

      if (is_string($alias)) {
        $column .= " AS " . $this->sanitizeField($alias);
      }
    }

    $this->columns = $columns;

    return $this;
  }

  public function join($table, $key, $operator = null, $value = null, $join_type = 'INNER') {
    if (!$key instanceof \Closure) {
      $key = function($join_builder) use($key, $operator, $value) {
        $join_builder->on($key, $operator, $value);
      };
    }

    $join_builder = new JoinBuilder($this->adapter, array(), $table, $join_type);
    $key($join_builder);

    $this->joins[] = $join_builder;
    return $this;
  }

  public function leftJoin($table, $key, $operator = null, $value = null) {
    $this->join($table, $key, $operator, $value, 'LEFT');
    return $this;
  }

  public function rightJoin($table, $key, $operator = null, $value = null) {
    $this->join($table, $key, $operator, $value, 'RIGHT');
    return $this;
  }

  public function leftOuterJoin($table, $key, $operator = null, $value = null) {
    $this->join($table, $key, $operator, $value, 'LEFT OUTER');
    return $this;
  }

  public function rightOuterJoin($table, $key, $operator = null, $value = null) {
    $this->join($table, $key, $operator, $value, 'RIGHT OUTER');
    return $this;
  }

  public function where($key, $operator = null, $value = null, $joiner = 'AND') {
    $this->where[] = compact('key', 'operator', 'value', 'joiner');
    return $this;
  }

  public function whereNot($key, $operator = null, $value = null) {
    $this->where($key, $operator, $value, 'AND NOT');
    return $this;
  }

  public function whereOr($key, $operator = null, $value = null) {
    $this->where($key, $operator, $value, 'OR');
    return $this;
  }

  public function whereOrNot($key, $operator = null, $value = null) {
    $this->where($key, $operator, $value, 'OR NOT');
    return $this;
  }

  public function groupby($columns) {
    if (!is_array($columns)) {
      $columns = array($columns);
    }

    foreach ($columns as $column) {
      $this->group_by[] = $this->sanitizeField($column);
    }

    return $this;
  }

  public function having($key, $operator = null, $value = null, $joiner = 'AND') {
    $this->having[] = compact('key', 'operator', 'value', 'joiner');
    return $this;
  }

  public function havingNot($key, $operator = null, $value = null) {
    $this->having($key, $operator, $value, 'AND NOT');
    return $this;
  }

  public function havingOr($key, $operator = null, $value = null) {
    $this->having($key, $operator, $value, 'OR');
    return $this;
  }

  public function havingOrNot($key, $operator = null, $value = null) {
    $this->having($key, $operator, $value, 'OR NOT');
    return $this;
  }

  public function orderby($columns, $default_dir = 'ASC') {
    if (!is_array($columns)) {
      $columns = array($columns);
    }

    foreach ($columns as $key => $value) {
      if (is_int($key)) {
        $field = $value;
        $dir = $default_dir;

      } else {
        $field = $key;
        $dir = $value;
      }

      if ($dir !== 'ASC' && $dir !== 'DESC') {
        throw new QueryBuilderException('Select::orderby(): order direction must be ASC or DESC, "' . $dir . '" given');
      }

      $field = $this->sanitizeField($field);
      $this->order_by[] = $field . " " . $dir;
    }

    return $this;
  }

  public function limit($row_count, $offset = null) {
    if (!is_numeric($row_count)) {
      throw new QueryBuilderException('Select::limit(): expected row_count as numeric, got ' . gettype($row_count));
    }

    $this->limit_row_count = $row_count;

    if (is_numeric($offset)) {
      $this->limit_offset = $offset;
    }

    return $this;
  }

  private function sanitizeField($field) {
    if ($field instanceof Raw) {
      return (string) $field;
    }

    return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
  }

  public function toSql() {
    $sql = "SELECT ";
    $params = array();

    if (count($this->columns) > 0) {
      $sql .= implode(", ", $this->columns) . "\n";
    } else {
      $sql .= "*\n";
    }

    if (is_array($this->table_name)) {
      list($table_name, $alias) = $this->table_name;
      $table_name = $this->sanitizeField($table_name) . " AS " . $this->sanitizeField($alias);

    } else {
      $table_name = $this->sanitizeField($this->table_name);
    }

    $sql .= "\tFROM " . $table_name . "\n";

    // Joins
    if (count($this->joins) > 0) {
      foreach ($this->joins as $join_builder) {
        $join = $join_builder->toSql();
        $sql .= "\t" . $join['sql'] . "\n";
      }
    }

    // Where
    if (count($this->where) > 0) {
      $criteria_builder = new CriteriaBuilder($this->adapter, $this->where);
      $where = $criteria_builder->toSql();

      $sql .= "\tWHERE " . $where['sql'] . "\n";
      $params = array_merge($params, $where['params']);
    }

    // Group by
    if (count($this->group_by) > 0) {
      $sql .= "\tGROUP BY " . implode(", ", $this->group_by) . "\n";
    }

    // Having
    if (count($this->having) > 0) {
      $criteria_builder = new CriteriaBuilder($this->adapter, $this->having);
      $having = $criteria_builder->toSql();

      $sql .= "\tHAVING " . $having['sql'] . "\n";
      $params = array_merge($params, $having['params']);
    }

    // Order by
    if (count($this->order_by) > 0) {
      $sql .= "\tORDER BY " . implode(", ", $this->order_by) . "\n";
    }

    // Limit
    if ($this->limit_row_count !== null) {
      $sql .= "\tLIMIT ";

      if ($this->limit_offset !== null) {
        $sql .= $this->limit_offset . ", ";
      }

      $sql .= $this->limit_row_count . "\n";
    }

    return compact('sql', 'params');
  }
}
?>