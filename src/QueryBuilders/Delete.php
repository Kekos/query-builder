<?php declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * Delete class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;

class Delete extends CriteriaBase
{
    private function sanitizeField($field)
    {
        if ($field instanceof Raw) {
            return (string)$field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    public function toSql(): Raw
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
        if (count($this->where) > 0) {
            $criteria_builder = new CriteriaBuilder($this->adapter, $this->where);
            $where = $criteria_builder->toSql();

            $sql .= "\n\tWHERE " . $where;
            $params = $where->getParams();
        }

        return new Raw($sql, $params);
    }
}
