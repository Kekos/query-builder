<?php
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
use QueryBuilder\QueryBuilderException;

class CriteriaBuilder
{
    protected $statements;
    protected $adapter;

    public function __construct(AdapterInterface $adapter, $statements = [])
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
    public function where($key, $operator = null, $value = null, $joiner = 'AND')
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
    public function whereNot($key, $operator = null, $value = null)
    {
        $this->where($key, $operator, $value, 'AND NOT');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @return static
     */
    public function whereIsNull($key)
    {
        $this->where($key, 'IS NULL');

        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @return static
     */
    public function whereIsNotNull($key)
    {
        $this->whereNot($key, 'IS NULL');

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
        $left_sanitized = $this->sanitizeField($left);
        $right_sanitized = $this->sanitizeField($right);

        if (!in_array($operator, ['=', '!=', '>', '>=', '<', '<='], true)) {
            throw new QueryBuilderException('Operator "' . $operator . '" is not allowed for column matching');
        }

        $this->where(new Raw($left_sanitized . ' ' . $operator . ' ' . $right_sanitized), null, null, $joiner);

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
        $this->whereColumnsEquals($left, $right, '!=');

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
     * @param string|Closure|Raw $key
     * @return static
     */
    public function whereOrIsNull($key)
    {
        $this->whereOr($key, 'IS NULL');

        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @return static
     */
    public function whereOrIsNotNull($key)
    {
        $this->whereOrNot($key, 'IS NULL');

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
        $this->whereColumnsEquals($left, $right, '=', 'OR');

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
        $this->whereColumnsEquals($left, $right, '!=', 'OR');

        return $this;
    }

    /**
     * @return array[]
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * @param array[] $criteria
     * @return void
     * @throws QueryBuilderException
     */
    public function setStatements(array $criteria)
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

        $this->statements = $criteria;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->statements);
    }

    /**
     * @param string|Raw|Closure $field
     * @return ($field is Closure ? Closure : string)
     */
    protected function sanitizeField($field)
    {
        if ($field instanceof Raw) {
            return (string)$field;
        }

        if ($field instanceof Closure) {
            return $field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    public function toSql()
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
                $params = array_merge($params, $criteria['params']);

                $sql .= $statement['joiner'] . ' (' . $criteria['sql'] . ') ';
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

        return compact('sql', 'params');
    }
}
