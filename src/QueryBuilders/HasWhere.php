<?php
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

/**
 * @property CriteriaBuilder $where
 */
trait HasWhere
{
    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @param string $joiner
     * @return static
     */
    public function where($key, $operator = null, $value = null, $joiner = 'AND')
    {
        $this->where->where($key, $operator, $value, $joiner);

        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return static
     */
    public function whereNot($key, $operator = null, $value = null)
    {
        $this->where->whereNot($key, $operator, $value);

        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return static
     */
    public function whereOr($key, $operator = null, $value = null)
    {
        $this->where->whereOr($key, $operator, $value);

        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @return static
     */
    public function whereOrNot($key, $operator = null, $value = null)
    {
        $this->where->whereOrNot($key, $operator, $value);

        return $this;
    }

    /**
     * @return array[]
     */
    public function getWhere()
    {
        return $this->where->getStatements();
    }

    public function setWhere(array $criteria)
    {
        $this->where->setStatements($criteria);
    }
}
