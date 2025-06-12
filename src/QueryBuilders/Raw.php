<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

class Raw
{
    protected string $sql;
    /**
     * @var array<int, ?scalar>
     */
    protected array $params;

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
