<?php
/**
 * QueryBuilder for PHP
 *
 * CriteriaBuilder class
 *
 * @version 1.1
 * @date 2015-12-13
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;

class CriteriaBuilder {

  protected $statements;
  protected $adapter;

  public function __construct($adapter, $statements = array()) {
    $this->adapter = $adapter;
    $this->statements = $statements;
  }

  public function where($key, $operator = null, $value = null, $joiner = 'AND') {
    $this->statements[] = compact('key', 'operator', 'value', 'joiner');
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

  protected function sanitizeField($field) {
    if ($field instanceof Raw) {
      return (string) $field;
    } else if ($field instanceof \Closure) {
      return $field;
    }

    return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
  }

  public function toSql() {
    $sql = "";
    $params = array();

    foreach ($this->statements as $statement) {
      $key = $this->sanitizeField($statement['key']);
      $value = $statement['value'];

      if ($value === null && $key instanceof \Closure) {
        $criteria_builder = new CriteriaBuilder($this->adapter);
        $key($criteria_builder);

        $criteria = $criteria_builder->toSql();
        $params = array_merge($params, $criteria['params']);

        $sql .= $statement['joiner'] . ' (' . $criteria['sql'] . ') ';

      } else if (is_array($value)) {
        $placeholders = array();
        foreach ($value as $element) {
          $placeholders[] = '?';
          $params[] = $element;
        }

        $placeholders = implode(', ', $placeholders);
        $sql .= $statement['joiner'] . ' ' . $key . ' ' . $statement['operator'] . ' (' . $placeholders . ') ';

      } else if ($statement['key'] instanceof Raw) {
        $sql .= $statement['joiner'] . ' ' . $key . ' ';
        $params = array_merge($params, $statement['key']->getParams());

      } else if ($value === null) {
        $sql .= $statement['joiner'] . ' ' . $key . ' IS NULL ';

      } else {
        $params[] = $value;
        $sql .= $statement['joiner'] . ' ' . $key . ' ' . $statement['operator'] . ' ? ';
      }
    }

    // Remove leading AND and OR
    $sql = preg_replace('/^(\s?AND ?|\s?OR ?)/i', '', $sql);

    return compact('sql', 'params');
  }
}
?>