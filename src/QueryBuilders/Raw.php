<?php
/**
 * QueryBuilder for PHP
 *
 * Raw class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

class Raw {

  protected $sql;
  protected $params;

  public function __construct($sql, $params = array()) {
    $this->sql = $sql;
    $this->params = $params;
  }

  public function getParams() {
    return $this->params;
  }

  public function __toString() {
    return $this->sql;
  }
}
?>