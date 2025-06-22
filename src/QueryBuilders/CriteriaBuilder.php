<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use Closure;
use QueryBuilder\AdapterInterface;
use QueryBuilder\QueryBuilder;
use QueryBuilder\QueryBuilderException;

use function array_merge;
use function compact;
use function implode;
use function is_array;
use function preg_replace;
use function trim;
use function array_key_exists;
use function print_r;
use function sprintf;
use function substr;

/**
 * @phpstan-type StatementArrayType array<int, array{
 *       key: string|Closure|Raw,
 *       operator: ?string,
 *       value: ?mixed,
 *       joiner: string,
 *   }>
 */
class CriteriaBuilder
{
    /**
     * @var StatementArrayType
     */
    protected array $statements;

    protected AdapterInterface $adapter;

    /**
     * @param StatementArrayType $statements
     */
    public function __construct(AdapterInterface $adapter, array $statements = [])
    {
        $this->adapter = $adapter;
        $this->statements = $statements;
    }

    /**
     * @return $this
     */
    public function where(string|Closure|Raw $key, ?string $operator = null, mixed $value = null, string $joiner = 'AND'): self
    {
        $this->statements[] = compact('key', 'operator', 'value', 'joiner');

        return $this;
    }

    /**
     * @return $this
     */
    public function whereNot(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->where($key, $operator, $value, 'AND NOT');

        return $this;
    }

    /**
     * @return $this
     */
    public function whereOr(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->where($key, $operator, $value, 'OR');

        return $this;
    }

    /**
     * @return $this
     */
    public function whereOrNot(string|Closure|Raw $key, ?string $operator = null, mixed $value = null): self
    {
        $this->where($key, $operator, $value, 'OR NOT');

        return $this;
    }

    /**
     * @return StatementArrayType
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * @param StatementArrayType $criteria
     * @throws QueryBuilderException
     */
    public function setStatements(array $criteria): void
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
                        substr(print_r($criterion, true), 7, -2), // Quick and dirty way to ignore the "Array(" prefix and ")" suffix
                    ));
                }
            }
        }

        $this->statements = $criteria;
    }

    public function isEmpty(): bool
    {
        return empty($this->statements);
    }

    /**
     * @return ($field is Closure ? Closure : string)
     */
    protected function sanitizeField(string|Closure|Raw $field): string|Closure
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
                $criteria_builder = new self($this->adapter);
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
