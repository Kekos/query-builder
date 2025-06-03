<?php

declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * JoinBuilder class
 *
 * @version 1.1
 * @date 2017-12-31
 */

namespace QueryBuilder\QueryBuilders;

use Closure;
use QueryBuilder\AdapterInterface;

class JoinBuilder extends CriteriaBuilder
{
    /**
     * @var string|Raw|array{0: string, 1: string}
     */
    protected $table;
    /**
     * @var string
     */
    protected $join_type;

    /**
     * @param array<int, array{
     *        key: string|Closure|Raw,
     *        operator: ?string,
     *        value: ?mixed,
     *        joiner: string,
     *    }> $statements
     * @param string|Raw|array{0: string, 1: string} $table
     */
    public function __construct(AdapterInterface $adapter, array $statements, $table, string $join_type)
    {
        parent::__construct($adapter, $statements);

        $this->table = $table;
        $this->join_type = $join_type;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function on($key, ?string $operator = null, $value = null, string $joiner = 'AND'): self
    {
        $this->where($key, $operator, $value, $joiner);
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function onOr($key, ?string $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR');
        return $this;
    }

    public function toSql(): Raw
    {
        $upstream_sql = parent::toSql();
        $params = $upstream_sql->getParams();

        if (is_array($this->table)) {
            [$table, $alias] = $this->table;
            $table = $this->sanitizeField($table) . " AS " . $this->sanitizeField($alias);
        } elseif ($this->table instanceof Raw) {
            $table = (string) $this->table;
            $params = array_merge($this->table->getParams(), $params);
        } else {
            $table = $this->sanitizeField($this->table);
        }

        $sql = $this->join_type . " JOIN " . $table . " ON " . $upstream_sql;

        return new Raw($sql, $params);
    }
}
