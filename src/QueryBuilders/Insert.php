<?php
/**
 * QueryBuilder for PHP
 *
 * Insert class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;
use QueryBuilder\QueryBuilderException;

class Insert extends VerbBase {

  private $values = array();

  public function values($values) {
    if (!is_array($values)) {
      throw new QueryBuilderException('Insert::values(): expected values as array, got ' . gettype($values));
    }

    $this->values = $values;

    return $this;
  }

  private function sanitizeField($field) {
    if ($field instanceof Raw) {
      return (string) $field;
    }

    return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
  }

  public function toSql() {
    $sql = "INSERT INTO ";
    $params = array();
    $placeholders = array();

    // Table name
    if (is_array($this->table_name)) {
      list($table_name, $alias) = $this->table_name;
      $sql .= $this->sanitizeField($table_name) . " AS " . $this->sanitizeField($alias);

    } else {
      $sql .= $this->sanitizeField($this->table_name);
    }

    // Columns
    $columns = array_keys($this->values);
    foreach ($columns as &$column) {
      $column = $this->sanitizeField($column);
    }

    $sql .= " (" . implode(", ", $columns) . ")\n";

    // Values
    $sql .= "\tVALUES (";
    foreach ($this->values as $value) {
      $params[] = $value;
      $placeholders[] = '?';
    }

    $sql .= implode(", ", $placeholders) . ")";

    return compact('sql', 'params');
  }
}
?>