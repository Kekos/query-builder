<?php

declare(strict_types=1);
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
    /**
     * @var array<int|string, string|Raw>
     */
    private $columns = [];
    /**
     * @var JoinBuilder[]
     */
    private $joins = [];
    /**
     * @var array<int, string|Raw>
     */
    private $group_by = [];
    /**
     * @var array<int, array{
     *       key: string|Closure|Raw,
     *       operator: ?string,
     *       value: ?mixed,
     *       joiner: string,
     *   }>
     */
    private $having = [];
    /**
     * @var array<int, string>
     */
    private $order_by = [];
    /**
     * @var ?int
     */
    private $limit_offset;
    /**
     * @var ?int
     */
    private $limit_row_count;

    /**
     * Adds columns to select
     *
     * @param string|array<string|int, string>|Raw|array<string|int, Raw> $columns Single column as string or multiple
     *  columns in array. Set column alias as array key.
     * @return $this
     */
    public function columns($columns): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    /**
     * @return array<int|string, string|Raw>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array<int|string, string|Raw> $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function join($table, $key, ?string $operator = null, $value = null, string $join_type = 'INNER'): self
    {
        if (!$key instanceof Closure) {
            $key = function (JoinBuilder $join_builder) use ($key, $operator, $value): void {
                $join_builder->on($key, $operator, $value);
            };
        }

        $join_builder = new JoinBuilder($this->adapter, [], $table, $join_type);
        $key($join_builder);

        $this->joins[] = $join_builder;
        return $this;
    }

    /**
     * @return JoinBuilder[]
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @param JoinBuilder[] $joins
     */
    public function setJoins(array $joins): void
    {
        $this->joins = $joins;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function leftJoin($table, $key, ?string $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'LEFT');
        return $this;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function rightJoin($table, $key, ?string $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'RIGHT');
        return $this;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function leftOuterJoin($table, $key, ?string $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'LEFT OUTER');
        return $this;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function rightOuterJoin($table, $key, ?string $operator = null, $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'RIGHT OUTER');
        return $this;
    }

    /**
     * @param string|array<int, string|Raw> $columns
     * @return $this
     */
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

    /**
     * @return array<int, string|Raw>
     */
    public function getGroupBy(): array
    {
        return $this->group_by;
    }

    /**
     * @param string|array<string|Raw> $columns
     */
    public function setGroupBy($columns): void
    {
        $this->group_by = [];
        $this->groupby($columns);
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function having($key, ?string $operator = null, $value = null, string $joiner = 'AND'): self
    {
        $this->having[] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function havingNot($key, ?string $operator = null, $value = null): self
    {
        $this->having($key, $operator, $value, 'AND NOT');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function havingOr($key, ?string $operator = null, $value = null): self
    {
        $this->having($key, $operator, $value, 'OR');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function havingOrNot($key, ?string $operator = null, $value = null): self
    {
        $this->having($key, $operator, $value, 'OR NOT');
        return $this;
    }

    /**
     * @param string|array<string|int, string|Raw> $columns Single column as string or multiple
     *  columns in array. Set column as array key and direction as value.
     * @param 'ASC'|'DESC' $default_dir Default sort direction, standard is "ASC"
     * @return $this
     * @throws QueryBuilderException
     */
    public function orderby($columns, string $default_dir = 'ASC'): self
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
     * @return array<int, string>
     */
    public function getOrderBy(): array
    {
        return $this->order_by;
    }

    /**
     * @param string|array<string|int, string|Raw> $columns
     * @param 'ASC'|'DESC' $default_dir
     * @throws QueryBuilderException
     */
    public function setOrderBy($columns, string $default_dir = 'ASC'): void
    {
        $this->order_by = [];
        $this->orderby($columns, $default_dir);
    }

    public function limit(int $row_count, ?int $offset = null): self
    {
        $this->limit_row_count = $row_count;

        if (is_numeric($offset)) {
            $this->limit_offset = $offset;
        }

        return $this;
    }

    public function getLimitOffset(): ?int
    {
        return $this->limit_offset;
    }

    public function getLimitRowCount(): ?int
    {
        return $this->limit_row_count;
    }

    /**
     * @param string|Raw $field
     */
    private function sanitizeField($field): string
    {
        if ($field instanceof Raw) {
            return (string) $field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    /**
     * @param string|Raw $field
     * @param array<int, ?scalar> $params
     */
    private function sanitizeFieldParam($field, array &$params): string
    {
        if ($field instanceof Raw) {
            $params = array_merge($params, $field->getParams());
        }

        return $this->sanitizeField($field);
    }

    /**
     * @param string|Raw $table_name
     * @param array<int, ?scalar> $params
     */
    private function tableNameToSql($table_name, array &$params): string
    {
        if ($table_name instanceof Raw) {
            return '(' . $this->sanitizeFieldParam($table_name, $params) . ')';
        }

        return $this->sanitizeFieldParam($table_name, $params);
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
            [$table_name, $alias] = $this->table_name;
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
