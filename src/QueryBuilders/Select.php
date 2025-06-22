<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use Closure;
use QueryBuilder\AdapterInterface;
use QueryBuilder\QueryBuilder;
use QueryBuilder\QueryBuilderException;

use function array_merge;
use function count;
use function implode;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;

class Select extends VerbBase
{
    use HasWhere;

    /**
     * @var array<int|string, string|Raw>
     */
    private array $columns = [];

    /**
     * @var JoinBuilder[]
     */
    private array $joins = [];

    private CriteriaBuilder $where;

    /**
     * @var array<int, string|Raw>
     */
    private array $group_by = [];

    private CriteriaBuilder $having;

    /**
     * @var array<int, string>
     */
    private array $order_by = [];

    private ?int $limit_offset = null;

    private ?int $limit_row_count = null;

    /**
     * @inheritDoc
     */
    public function __construct(string|array|Raw $table_name, AdapterInterface $adapter)
    {
        parent::__construct($table_name, $adapter);

        $this->where = new CriteriaBuilder($adapter);
        $this->having = new CriteriaBuilder($adapter);
    }

    /**
     * Adds columns to select
     *
     * @param string|array<string|int, string>|Raw|array<string|int, Raw> $columns Single column as string or multiple
     *                                                                             columns in array. Set column alias as array key.
     * @return $this
     */
    public function columns(string|array|Raw $columns): self
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
     * @return $this
     */
    public function join(string|array|Raw $table, string|Closure|Raw $key, ?string $operator = null, mixed $value = null, string $join_type = 'INNER'): self
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
     * @return $this
     */
    public function leftJoin(string|array|Raw $table, string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'LEFT');

        return $this;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @return $this
     */
    public function rightJoin(string|array|Raw $table, string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'RIGHT');

        return $this;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @return $this
     */
    public function leftOuterJoin(string|array|Raw $table, string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'LEFT OUTER');

        return $this;
    }

    /**
     * @param string|Raw|array{0: string, 1: string} $table
     * @return $this
     */
    public function rightOuterJoin(string|array|Raw $table, string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->join($table, $key, $operator, $value, 'RIGHT OUTER');

        return $this;
    }

    /**
     * @param string|array<int, string|Raw> $columns
     * @return $this
     */
    public function groupby(string|array $columns): self
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
    public function setGroupBy(string|array $columns): void
    {
        $this->group_by = [];
        $this->groupby($columns);
    }

    /**
     * @return $this
     */
    public function having(string|Closure|Raw $key, ?string $operator = null, mixed $value = null, string $joiner = 'AND'): self
    {
        $this->having->where($key, $operator, $value, $joiner);

        return $this;
    }

    /**
     * @return $this
     */
    public function havingNot(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->having->whereNot($key, $operator, $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function havingOr(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->having->whereOr($key, $operator, $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function havingOrNot(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->having->whereOrNot($key, $operator, $value);

        return $this;
    }

    /**
     * @param string|array<string|int, string|Raw> $columns Single column as string or multiple
     *                                                      columns in array. Set column as array key and direction as value.
     * @param 'ASC'|'DESC' $default_dir Default sort direction, standard is "ASC"
     * @return $this
     * @throws QueryBuilderException
     */
    public function orderby(string|array $columns, string $default_dir = 'ASC'): self
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
    public function setOrderBy(string|array $columns, string $default_dir = 'ASC'): void
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

    private function sanitizeField(string|Raw $field): string
    {
        if ($field instanceof Raw) {
            return (string) $field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    /**
     * @param array<int, ?scalar> $params
     */
    private function sanitizeFieldParam(string|Raw $field, array &$params): string
    {
        if ($field instanceof Raw) {
            $params = array_merge($params, $field->getParams());
        }

        return $this->sanitizeField($field);
    }

    /**
     * @param array<int, ?scalar> $params
     */
    private function tableNameToSql(string|Raw $table_name, array &$params): string
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
        if (!$this->where->isEmpty()) {
            $where = $this->where->toSql();

            $sql .= "\tWHERE " . $where . "\n";
            $params = array_merge($params, $where->getParams());
        }

        // Group by
        if (count($this->group_by) > 0) {
            $sql .= "\tGROUP BY " . implode(", ", $this->group_by) . "\n";
        }

        // Having
        if (!$this->having->isEmpty()) {
            $having = $this->having->toSql();

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
