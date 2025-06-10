<?php

declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * Raw class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

class Raw
{
    /**
     * @var string
     */
    protected $sql;
    /**
     * @var array<int, ?scalar>
     */
    protected $params;

    /**
     * @param array<int, ?scalar> $params
     */
    public function __construct(string $sql, array $params = [])
    {
        $this->sql = $sql;
        $this->params = $params;
    }

    /**
     * @return array<int, ?scalar>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function __toString(): string
    {
        return $this->sql;
    }
}
