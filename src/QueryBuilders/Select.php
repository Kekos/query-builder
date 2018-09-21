<?php declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * Select class
 *
 * @version 1.1
 * @date 2015-12-30
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;
use QueryBuilder\QueryBuilderException;

class Select extends CriteriaBase
{
    private $columns = [];
    /** @var JoinBuilder[] */
    private $joins = [];
    private $group_by = [];
    private $having = [];
    private $order_by = [];
    private $limit_offset = null;
    private $limit_row_count = null;

    public function columns($columns): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    public function join($table, $key, $operator = null, $value = null, $join_type = 'INNER'): self
    {
        if (!$key instanceof \Closure) {
            $key = function (JoinBuilder $join_builder) use ($key, $operator, $value): void {
                $join_builder->on($key, $operator, $value);
            };
        }

        $join_builder = new JoinBuilder($this->adapter, [], $table, $join_type);
        $key($join_builder);

        $this->joins[] = $join_builder;
        return $this;
    }

    public function leftJoin($table, $key, $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'LEFT');
        return $this;
    }

    public function rightJoin($table, $key, $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'RIGHT');
        return $this;
    }

    public function leftOuterJoin($table, $key, $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'LEFT OUTER');
        return $this;
    }

    public function rightOuterJoin($table, $key, $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'RIGHT OUTER');
        return $this;
    }

    public function groupby($columns): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $this->group_by[] = $this->sanitizeField($column);
        }

        return $this;
    }

    public function having($key, $operator = null, $value = null, $joiner = 'AND'): self
    {
        $this->having[] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    public function havingNot($key, $operator = null, $value = null): self
    {
        $this->having($key, $operator, $value, 'AND NOT');
        return $this;
    }

    public function havingOr($key, $operator = null, $value = null): self
    {
        $this->having($key, $operator, $value, 'OR');
        return $this;
    }

    public function havingOrNot($key, $operator = null, $value = null): self
    {
        $this->having($key, $operator, $value, 'OR NOT');
        return $this;
    }

    public function orderby($columns, $default_dir = 'ASC'): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
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

    public function limit(int $row_count, ?int $offset = null): self
    {
        $this->limit_row_count = $row_count;

        if (is_numeric($offset)) {
            $this->limit_offset = $offset;
        }

        return $this;
    }

    private function sanitizeField($field): string
    {
        if ($field instanceof Raw) {
            return (string)$field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    private function sanitizeFieldParam($field, array &$params): string
    {
        if ($field instanceof Raw) {
            $params = array_merge($params, $field->getParams());
        }

        return $this->sanitizeField($field);
    }

    public function toSql(): Raw
    {
        $sql = "SELECT ";
        $params = [];

        if (count($this->columns) > 0) {
            $sanitized_columns = [];
            foreach ($this->columns as $alias => $column) {
                $column = $this->sanitizeFieldParam($column, $params);

                if (is_string($alias)) {
                    $column .= " AS " . $this->sanitizeField($alias);
                }

                $sanitized_columns[] = $column;
            }

            $sql .= implode(", ", $sanitized_columns) . "\n";
        } else {
            $sql .= "*\n";
        }

        if (is_array($this->table_name)) {
            list($table_name, $alias) = $this->table_name;
            $table_name = $this->sanitizeFieldParam($table_name, $params)
                . " AS "
                . $this->sanitizeField($alias);
        } else {
            $table_name = $this->sanitizeFieldParam($this->table_name, $params);
        }

        $sql .= "\tFROM " . $table_name . "\n";

        // Joins
        if (count($this->joins) > 0) {
            foreach ($this->joins as $join_builder) {
                $join = $join_builder->toSql();
                $sql .= "\t" . $join . "\n";
                $params = array_merge($params, $join->getParams());
            }
        }

        // Where
        if (count($this->where) > 0) {
            $criteria_builder = new CriteriaBuilder($this->adapter, $this->where);
            $where = $criteria_builder->toSql();

            $sql .= "\tWHERE " . $where . "\n";
            $params = array_merge($params, $where->getParams());
        }

        // Group by
        if (count($this->group_by) > 0) {
            $sql .= "\tGROUP BY " . implode(", ", $this->group_by) . "\n";
        }

        // Having
        if (count($this->having) > 0) {
            $criteria_builder = new CriteriaBuilder($this->adapter, $this->having);
            $having = $criteria_builder->toSql();

            $sql .= "\tHAVING " . $having . "\n";
            $params = array_merge($params, $having->getParams());
        }

        // Order by
        if (count($this->order_by) > 0) {
            $sql .= "\tORDER BY " . implode(", ", $this->order_by) . "\n";
        }

        // Limit
        if ($this->limit_row_count !== null) {
            $sql .= "\tLIMIT ";

            if ($this->limit_offset !== null) {
                $sql .= "?, ";
                $params[] = $this->limit_offset;
            }

            $sql .= "?\n";
            $params[] = $this->limit_row_count;
        }

        return new Raw($sql, $params);
    }
}
