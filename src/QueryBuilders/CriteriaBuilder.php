<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use Closure;
use QueryBuilder\AdapterInterface;
use QueryBuilder\QueryBuilder;

class CriteriaBuilder
{
    /**
     * @var array<int, array{
     *      key: string|Closure|Raw,
     *      operator: ?string,
     *      value: ?mixed,
     *      joiner: string,
     *  }>
     */
    protected $statements;
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @param array<int, array{
     *       key: string|Closure|Raw,
     *       operator: ?string,
     *       value: ?mixed,
     *       joiner: string,
     *   }> $statements
     */
    public function __construct(AdapterInterface $adapter, array $statements = [])
    {
        $this->adapter = $adapter;
        $this->statements = $statements;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function where($key, ?string $operator = null, $value = null, string $joiner = 'AND'): self
    {
        $this->statements[] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function whereNot($key, ?string $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'AND NOT');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function whereOr($key, ?string $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR');
        return $this;
    }

    /**
     * @param string|Closure|Raw $key
     * @param mixed|null $value
     * @return $this
     */
    public function whereOrNot($key, ?string $operator = null, $value = null): self
    {
        $this->where($key, $operator, $value, 'OR NOT');
        return $this;
    }

    /**
     * @param string|Closure|Raw $field
     * @return ($field is Closure ? Closure : string)
     */
    protected function sanitizeField($field)
    {
        if ($field instanceof Raw) {
            return (string) $field;
        }

        if ($field instanceof Closure) {
            return $field;
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

            if ($key instanceof Closure) {
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
