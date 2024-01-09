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
    public function where($key, $operator = null, $value = null, $joiner = 'AND')
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
    public function whereNot($key, $operator = null, $value = null)
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
    public function whereOr($key, $operator = null, $value = null)
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
    public function whereOrNot($key, $operator = null, $value = null)
    {
        $this->where($key, $operator, $value, 'OR NOT');
        return $this;
    }

    /**
     * @return array[]
     */
    public function getWhere()
    {
        return $this->where;
    }

    public function setWhere(array $criteria)
    {
        $required_keys = [
            'key',
            'operator',
            'value',
            'joiner',
        ];

        foreach ($criteria as $ix => $criterion) {
            foreach ($required_keys as $required_key) {
                if (!array_key_exists($required_key, $criterion)) {
                    throw new QueryBuilderException(sprintf(
                        'Missing the required key `%s` in criterion array index %s: %s',
                        $required_key,
                        $ix,
                        substr(print_r($criterion, true), 7, -2) // Quick and dirty way to ignore the "Array(" prefix and ")" suffix
                    ));
                }
            }
        }

        $this->where = $criteria;
    }
}
