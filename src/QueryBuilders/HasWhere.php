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
use QueryBuilder\QueryBuilderException;

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
     * @return static
     */
    public function whereIsNull($key)
    {
        $this->where->whereIsNull($key);

        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @return static
     */
    public function whereIsNotNull($key)
    {
        $this->where->whereIsNotNull($key);

        return $this;
    }

    /**
     * @param string $left
     * @param string $right
     * @param string $operator
     * @param string $joiner
     * @return static
     * @throws QueryBuilderException
     */
    public function whereColumnsEquals($left, $right, $operator = '=', $joiner = 'AND')
    {
        $this->where->whereColumnsEquals($left, $right, $operator, $joiner);

        return $this;
    }

    /**
     * @param string $left
     * @param string $right
     * @return static
     * @throws QueryBuilderException
     */
    public function whereColumnsNotEquals($left, $right)
    {
        $this->where->whereColumnsNotEquals($left, $right);

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
     * @param string|Closure|Raw $key
     * @return static
     */
    public function whereOrIsNull($key)
    {
        $this->where->whereOrIsNull($key);

        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @return static
     */
    public function whereOrIsNotNull($key)
    {
        $this->where->whereOrIsNotNull($key);

        return $this;
    }

    /**
     * @param string $left
     * @param string $right
     * @return static
     * @throws QueryBuilderException
     */
    public function whereOrColumnsEquals($left, $right)
    {
        $this->where->whereOrColumnsEquals($left, $right);

        return $this;
    }

    /**
     * @param string $left
     * @param string $right
     * @return static
     * @throws QueryBuilderException
     */
    public function whereOrColumnsNotEquals($left, $right)
    {
        $this->where->whereOrColumnsNotEquals($left, $right);

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
