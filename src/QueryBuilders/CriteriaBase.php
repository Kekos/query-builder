<?php

declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * CriteriaBase class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

use Closure;

abstract class CriteriaBase extends VerbBase
{
    /**
     * @var array<int, array{
     *     key: string|Closure|Raw,
     *     operator: ?string,
     *     value: ?mixed,
     *     joiner: string,
     * }>
     */
    protected $where = [];

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return static
     */
    public function where($key, ?string $operator = null, $value = null, string $joiner = 'AND'): self
    {
        $this->where[] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return static
     */
    public function whereNot($key, ?string $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'AND NOT');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return static
     */
    public function whereOr($key, ?string $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return static
     */
    public function whereOrNot($key, ?string $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR NOT');
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
        return $this->where;
    }
}
