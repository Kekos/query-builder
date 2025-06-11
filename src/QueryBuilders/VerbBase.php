<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\AdapterInterface;

abstract class VerbBase
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;
    /**
     * @var string|Raw|array{0: string|Raw, 1: string}
     */
    protected $table_name;

    /**
     * @param string|Raw|array{0: string|Raw, 1: string} $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @param AdapterInterface $adapter
     */
    public function __construct($table_name, AdapterInterface $adapter)
    {
        $this->table_name = $table_name;
        $this->adapter = $adapter;
    }

    /**
     * @return string|Raw|array{0: string|Raw, 1: string}
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @param string $alias New table alias
     * @return $this
     */
    public function alias(string $alias): self
    {
        if (is_array($this->table_name)) {
            $this->table_name[1] = $alias;
        } else {
            $this->table_name = [$this->table_name, $alias];
        }

        return $this;
    }

    public function getAlias(): ?string
    {
        if (!is_array($this->table_name)) {
            return null;
        }

        return $this->table_name[1];
    }

    abstract public function toSql(): Raw;
}
