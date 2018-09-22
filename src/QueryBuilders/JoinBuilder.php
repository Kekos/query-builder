<?php declare(strict_types=1);
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
    protected $table;
    protected $join_type;

    public function __construct(AdapterInterface $adapter, array $statements, $table, string $join_type)
    {
        parent::__construct($adapter, $statements);

        $this->table = $table;
        $this->join_type = $join_type;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @param string $joiner
     * @return static
     */
    public function on($key, $operator = null, $value = null, $joiner = 'AND'): self
    {
        $this->where($key, $operator, $value, $joiner);
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return static
     */
    public function onOr($key, $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR');
        return $this;
    }

    public function toSql(): Raw
    {
        $upstream_sql = parent::toSql();
        $params = $upstream_sql->getParams();

        if (is_array($this->table)) {
            list($table, $alias) = $this->table;
            $table = $this->sanitizeField($table) . " AS " . $this->sanitizeField($alias);
        } else {
            if ($this->table instanceof Raw) {
                $table = (string)$this->table;
                $params = array_merge($params, $this->table->getParams());
            } else {
                $table = $this->sanitizeField($this->table);
            }
        }

        $sql = $this->join_type . " JOIN " . $table . " ON " . $upstream_sql;

        return new Raw($sql, $params);
    }
}
