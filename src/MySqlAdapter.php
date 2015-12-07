<?php
/**
 * QueryBuilder for PHP
 *
 * MySqlAdapter class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder;

class MySqlAdapter implements AdapterInterface {

  public function getSanitizer() {
    return '`';
  }
}
?>