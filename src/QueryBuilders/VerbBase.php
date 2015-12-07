<?php
/**
 * QueryBuilder for PHP
 *
 * VerbBase class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;

abstract class VerbBase {

  protected $adapter;
  protected $table_name;

  public function __construct($table_name, $adapter) {
    $this->table_name = $table_name;
    $this->adapter = $adapter;
  }
}
?>