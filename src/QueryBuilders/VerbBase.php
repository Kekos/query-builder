<?php declare(strict_types=1);
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

    public function __construct($table_name, AdapterInterface $adapter)
    {
        $this->table_name = $table_name;
        $this->adapter = $adapter;
    }

    public function alias($alias): self
    {
        if (is_array($this->table_name)) {
            $this->table_name[1] = $alias;
        } else {
            $this->table_name = [$this->table_name, $alias];
        }

        return $this;
    }

    abstract public function toSql(): Raw;
}
