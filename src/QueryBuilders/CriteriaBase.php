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
    protected $where = [];

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @param string $joiner
     * @return static
     */
    public function where($key, $operator = null, $value = null, $joiner = 'AND'): self
    {
        $this->where[] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return static
     */
    public function whereNot($key, $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'AND NOT');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return static
     */
    public function whereOr($key, $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return static
     */
    public function whereOrNot($key, $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR NOT');
        return $this;
    }
}
