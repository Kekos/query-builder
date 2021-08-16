<?php
/**
 * QueryBuilder for PHP
 *
 * Select class
 *
 * @version 1.1
 * @date 2015-12-30
 */

namespace QueryBuilder\QueryBuilders;

use Closure;
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

    /**
     * Adds columns to select
     *
     * @param string|string[] $columns Single column as string or multiple
     *  columns in array. Set column alias as array key.
     * @return Select
     */
    public function columns($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    /**
     * @return array<string, string>|string[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string|string[] $table
     * @param string|Closure $key
     * @param string|null $operator
     * @param mixed|null $value
     * @param string $join_type
     * @return Select
     */
    public function join($table, $key, $operator = null, $value = null, $join_type = 'INNER')
    {
        if (!$key instanceof Closure) {
            $key = function ($join_builder) use ($key, $operator, $value) {
                $join_builder->on($key, $operator, $value);
            };
        }

        $join_builder = new JoinBuilder($this->adapter, [], $table, $join_type);
        $key($join_builder);

        $this->joins[] = $join_builder;
        return $this;
    }

    /**
     * @param string|string[] $table
     * @param string|Closure $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return Select
     */
    public function leftJoin($table, $key, $operator = null, $value = null)
    {
        $this->join($table, $key, $operator, $value, 'LEFT');
        return $this;
    }

    /**
     * @param string|string[] $table
     * @param string|Closure $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return Select
     */
    public function rightJoin($table, $key, $operator = null, $value = null)
    {
        $this->join($table, $key, $operator, $value, 'RIGHT');
        return $this;
    }

    /**
     * @param string|string[] $table
     * @param string|Closure $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return Select
     */
    public function leftOuterJoin($table, $key, $operator = null, $value = null)
    {
        $this->join($table, $key, $operator, $value, 'LEFT OUTER');
        return $this;
    }

    /**
     * @param string|string[] $table
     * @param string|Closure $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return Select
     */
    public function rightOuterJoin($table, $key, $operator = null, $value = null)
    {
        $this->join($table, $key, $operator, $value, 'RIGHT OUTER');
        return $this;
    }

    /**
     * @param string|string[] $columns
     * @return Select
     */
    public function groupby($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $this->group_by[] = $this->sanitizeField($column);
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getGroupBy()
    {
        return $this->group_by;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @param string $joiner
     * @return Select
     */
    public function having($key, $operator = null, $value = null, $joiner = 'AND')
    {
        $this->having[] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return Select
     */
    public function havingNot($key, $operator = null, $value = null)
    {
        $this->having($key, $operator, $value, 'AND NOT');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return Select
     */
    public function havingOr($key, $operator = null, $value = null)
    {
        $this->having($key, $operator, $value, 'OR');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return Select
     */
    public function havingOrNot($key, $operator = null, $value = null)
    {
        $this->having($key, $operator, $value, 'OR NOT');
        return $this;
    }

    /**
     * @param string|string[] $columns Single column as string or multiple
     *  columns in array. Set column as array key and direction as value.
     * @param string $default_dir Default sort direction, standard is "ASC"
     * @return Select
     * @throws QueryBuilderException
     */
    public function orderby($columns, $default_dir = 'ASC')
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

    /**
     * @return string[]
     */
    public function getOrderBy()
    {
        return $this->order_by;
    }

	/**
	 * @param int $row_count
	 * @param int|null $offset
	 * @return Select
	 * @throws QueryBuilderException
	 */
    public function limit($row_count, $offset = null)
    {
        if (!is_numeric($row_count)) {
            throw new QueryBuilderException('Select::limit(): expected row_count as numeric, got ' . gettype($row_count));
        }

        $this->limit_row_count = $row_count;

        if (is_numeric($offset)) {
            $this->limit_offset = $offset;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimitOffset()
    {
        return $this->limit_offset;
    }

    /**
     * @return int|null
     */
    public function getLimitRowCount()
    {
        return $this->limit_row_count;
    }

    private function sanitizeField($field)
    {
        if ($field instanceof Raw) {
            return (string)$field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

	/**
	 * @param string|Raw $field
	 * @param array $params
	 * @return string
	 */
    private function sanitizeFieldParam($field, array &$params)
    {
        if ($field instanceof Raw) {
            $params = array_merge($params, $field->getParams());
        }

        return $this->sanitizeField($field);
    }

    /**
     * @param string|Raw $table_name
     * @param array $params
     * @return string
     */
    private function tableNameToSql($table_name, &$params)
    {
        if ($table_name instanceof Raw) {
            return '(' . $this->sanitizeFieldParam($table_name, $params) . ')';
        }

        return $this->sanitizeFieldParam($table_name, $params);
    }

    public function toSql()
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
            $table_name = $this->tableNameToSql($table_name, $params)
                . " AS "
                . $this->sanitizeField($alias);
        } else {
            $table_name = $this->tableNameToSql($this->table_name, $params);
        }

        $sql .= "\tFROM " . $table_name . "\n";

        // Joins
        if (count($this->joins) > 0) {
            foreach ($this->joins as $join_builder) {
                $join = $join_builder->toSql();
                $sql .= "\t" . $join['sql'] . "\n";
                $params = array_merge($params, $join['params']);
            }
        }

        // Where
        if (count($this->where) > 0) {
            $criteria_builder = new CriteriaBuilder($this->adapter, $this->where);
            $where = $criteria_builder->toSql();

            $sql .= "\tWHERE " . $where['sql'] . "\n";
            $params = array_merge($params, $where['params']);
        }

        // Group by
        if (count($this->group_by) > 0) {
            $sql .= "\tGROUP BY " . implode(", ", $this->group_by) . "\n";
        }

        // Having
        if (count($this->having) > 0) {
            $criteria_builder = new CriteriaBuilder($this->adapter, $this->having);
            $having = $criteria_builder->toSql();

            $sql .= "\tHAVING " . $having['sql'] . "\n";
            $params = array_merge($params, $having['params']);
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

        return compact('sql', 'params');
    }
}
