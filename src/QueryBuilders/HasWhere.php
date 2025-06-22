<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use Closure;
use QueryBuilder\QueryBuilderException;

/**
 * @property CriteriaBuilder $where
 */
trait HasWhere
{
    /**
     * @return $this
     */
    public function where(string|Closure|Raw $key, ?string $operator = null, mixed $value = null, string $joiner = 'AND'): self
    {
        $this->where->where($key, $operator, $value, $joiner);

        return $this;
    }

    /**
     * @return $this
     */
    public function whereNot(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->where->whereNot($key, $operator, $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function whereOr(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->where->whereOr($key, $operator, $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function whereOrNot(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->where->whereOrNot($key, $operator, $value);

        return $this;
    }

    /**
     * @return array<int, array{
     *      key: string|Closure|Raw,
     *      operator: ?string,
     *      value: ?mixed,
     *      joiner: string,
     *  }>
     */
    public function getWhere(): array
    {
        return $this->where->getStatements();
    }

    /**
     * @param array<int, array{
     *      key: string|Closure|Raw,
     *      operator: ?string,
     *      value: ?mixed,
     *      joiner: string,
     *  }> $criteria
     * @throws QueryBuilderException
     */
    public function setWhere(array $criteria): void
    {
        $this->where->setStatements($criteria);
    }
}
