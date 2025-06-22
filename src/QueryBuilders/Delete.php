<?php
/**
 * QueryBuilder for PHP
 *
 * Delete class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\AdapterInterface;
use QueryBuilder\QueryBuilder;

class Delete extends VerbBase
{
    use HasWhere;

    /** @var CriteriaBuilder */
    private $where;

    public function __construct($table_name, AdapterInterface $adapter)
    {
        parent::__construct($table_name, $adapter);

        $this->where = new CriteriaBuilder($adapter);
    }

    private function sanitizeField($field)
    {
        if ($field instanceof Raw) {
            return (string)$field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    public function toSql()
    {
        $sql = "DELETE FROM ";
        $params = [];

        // Table name
        if (is_array($this->table_name)) {
            list($table_name, $alias) = $this->table_name;
            $sql .= $this->sanitizeField($table_name) . " AS " . $this->sanitizeField($alias);
        } else {
            $sql .= $this->sanitizeField($this->table_name);
        }

        // Where
        if (!$this->where->isEmpty()) {
            $where = $this->where->toSql();

            $sql .= "\n\tWHERE " . $where['sql'];
            $params = $where['params'];
        }

        return compact('sql', 'params');
    }
}
