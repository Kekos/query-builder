<?php
/**
 * QueryBuilder for PHP
 *
 * Update class
 *
 * @version 1.1
 * @date 2015-12-29
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;
use QueryBuilder\QueryBuilderException;

class Update extends CriteriaBase
{
    private $values = [];

    public function set($values)
    {
        if (!is_array($values)) {
            throw new QueryBuilderException('Update::values(): expected values as array, got ' . gettype($values));
        }

        $this->values = $values;

        return $this;
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
        $sql = "UPDATE ";
        $params = [];
        $placeholders = [];

        // Table name
        if (is_array($this->table_name)) {
            list($table_name, $alias) = $this->table_name;
            $sql .= $this->sanitizeField($table_name) . " AS " . $this->sanitizeField($alias);
        } else {
            $sql .= $this->sanitizeField($this->table_name);
        }

        // SET
        $sql .= "\n\tSET\n";
        foreach ($this->values as $column => $value) {
            if ($value instanceof Raw) {
                $params = array_merge($params, $value->getParams());
                $placeholders[] = "\t\t" . $this->sanitizeField($column) . " = " . $value;
            } else {
                $params[] = $value;
                $placeholders[] = "\t\t" . $this->sanitizeField($column) . " = ?";
            }
        }

        $sql .= implode(",\n", $placeholders) . "\n";

        // Where
        if (count($this->where) > 0) {
            $criteria_builder = new CriteriaBuilder($this->adapter, $this->where);
            $where = $criteria_builder->toSql();

            $sql .= "\tWHERE " . $where['sql'];
            $params = array_merge($params, $where['params']);
        }

        return compact('sql', 'params');
    }
}
