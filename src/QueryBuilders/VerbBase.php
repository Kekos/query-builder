<?php
/**
 * QueryBuilder for PHP
 *
 * VerbBase class
 *
 * @version 1.1
 * @date 2015-12-13
 */

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\AdapterInterface;

abstract class VerbBase
{
    /** @var AdapterInterface */
    protected $adapter;
    /** @var Raw|string|string[] */
    protected $table_name;

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @param AdapterInterface $adapter
     */
    public function __construct($table_name, AdapterInterface $adapter)
    {
        if (is_array($table_name) && isset($table_name['sql'], $table_name['params'])) {
            $this->table_name = new Raw($table_name['sql'], $table_name['params']);
        } else {
            $this->table_name = $table_name;
        }

        $this->adapter = $adapter;
    }

    /**
     * @return Raw|string|string[]
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @param string $alias New table alias
     * @return static
     */
    public function alias($alias)
    {
        if (is_array($this->table_name)) {
            $this->table_name[1] = $alias;
        } else {
            $this->table_name = [$this->table_name, $alias];
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlias()
    {
        if (!is_array($this->table_name) || !isset($this->table_name[1])) {
            return null;
        }

        return $this->table_name[1];
    }

    /**
     * @return array{sql: string, params: array<int, scalar>}
     */
    abstract public function toSql();
}
