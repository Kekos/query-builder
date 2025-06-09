<?php

declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * CriteriaBuilder class
 *
 * @version 1.1
 * @date 2015-12-13
 */

namespace QueryBuilder\QueryBuilders;

use Closure;
use QueryBuilder\AdapterInterface;
use QueryBuilder\QueryBuilder;

class CriteriaBuilder
{
    protected $statements;
    protected $adapter;

    public function __construct(AdapterInterface $adapter, array $statements = [])
    {
        $this->adapter = $adapter;
        $this->statements = $statements;
    }

    /**
     * @param string|Closure|Raw $key
     * @param string|null $operator
     * @param mixed|null $value
     * @param string $joiner
     * @return static
     */
    public function where($key, $operator = null, $value = null, $joiner = 'AND'): self
    {
        $this->statements[] = compact('key', 'operator', 'value', 'joiner');
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

    protected function sanitizeField($field)
    {
        if ($field instanceof Raw) {
            return (string) $field;
        } else {
            if ($field instanceof Closure) {
                return $field;
            }
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    public function toSql(): Raw
    {
        $sql = "";
        $params = [];

        foreach ($this->statements as $statement) {
            $key = $this->sanitizeField($statement['key']);
            $value = $statement['value'];

            if ($value === null && $key instanceof Closure) {
                $criteria_builder = new CriteriaBuilder($this->adapter);
                $key($criteria_builder);

                $criteria = $criteria_builder->toSql();
                $params = array_merge($params, $criteria->getParams());

                $sql .= $statement['joiner'] . ' (' . $criteria . ') ';
            } else {
                if (is_array($value)) {
                    $sql .= $statement['joiner'] . ' ' . $key . ' ' . $statement['operator'];

                    if ($statement['operator'] === 'BETWEEN') {
                        $params = array_merge($params, $value);
                        $sql .= ' ? AND ? ';
                    } else {
                        $placeholders = [];
                        foreach ($value as $element) {
                            $placeholders[] = '?';
                            $params[] = $element;
                        }

                        $placeholders = implode(', ', $placeholders);
                        $sql .= ' (' . $placeholders . ') ';
                    }
                } else {
                    if ($statement['key'] instanceof Raw) {
                        $sql .= $statement['joiner'] . ' ' . $key . ' ';
                        $params = array_merge($params, $statement['key']->getParams());
                    } else {
                        if ($value === null) {
                            $sql .= $statement['joiner'] . ' ' . $key . ' IS NULL ';
                        } else {
                            $params[] = $value;
                            $sql .= $statement['joiner'] . ' ' . $key . ' ' . $statement['operator'] . ' ? ';
                        }
                    }
                }
            }
        }

        // Remove leading AND and OR
        $sql = preg_replace('/^(\s?AND ?|\s?OR ?)/i', '', $sql);
        $sql = trim($sql);

        return new Raw($sql, $params);
    }
}
