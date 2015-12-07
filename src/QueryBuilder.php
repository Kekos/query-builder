<?php
/**
 * QueryBuilder for PHP
 *
 * QueryBuilder class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder;

class QueryBuilder {

  private static $adapter;

  private function __construct() {}

  public static function setAdapter($adapter) {
    self::$adapter = $adapter;
  }

  public static function select($table_name) {
    return new QueryBuilders\Select($table_name, self::$adapter);
  }

  public static function insert($table_name) {
    return new QueryBuilders\Insert($table_name, self::$adapter);
  }

  public static function update($table_name) {
    return new QueryBuilders\Update($table_name, self::$adapter);
  }

  public static function delete($table_name) {
    return new QueryBuilders\Delete($table_name, self::$adapter);
  }

  public static function raw($sql, $params = array()) {
    return new QueryBuilders\Raw($sql, $params);
  }

  public static function sanitizeField($field, $sanitizer) {
    if (is_string($field)) {
      $field_parts = explode('.', $field);

      foreach ($field_parts as $key => $field) {
        $key = trim($key);
        if ($key != '*') {
          $field_parts[$key] = $sanitizer . $field . $sanitizer;
        }
      }

      return implode('.', $field_parts);
    }

    return $field;
  }
}
?>