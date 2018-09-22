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
    protected $adapter;
    protected $table_name;

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @param AdapterInterface $adapter
     */
    public function __construct($table_name, AdapterInterface $adapter)
    {
        $this->table_name = $table_name;
        $this->adapter = $adapter;
    }

    /**
     * @param string $alias New table alias
     * @return VerbBase
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
}
