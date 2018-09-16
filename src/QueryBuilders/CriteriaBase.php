<?php declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * CriteriaBase class
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder\QueryBuilders;

abstract class CriteriaBase extends VerbBase
{
    protected $where = [];

    public function where($key, $operator = null, $value = null, $joiner = 'AND'): self
    {
        $this->where[] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    public function whereNot($key, $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'AND NOT');
        return $this;
    }

    public function whereOr($key, $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR');
        return $this;
    }

    public function whereOrNot($key, $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR NOT');
        return $this;
    }
}
