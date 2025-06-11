<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use Closure;
use QueryBuilder\QueryBuilderException;

use function array_key_exists;

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
     * @return $this
     */
    public function where($key, ?string $operator = null, $value = null, string $joiner = 'AND'): self
    {
        $this->where[] = compact('key', 'operator', 'value', 'joiner');
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

    /**
     * @param array<int, array{
     *      key: string|Closure|Raw,
     *      operator: ?string,
     *      value: ?mixed,
     *      joiner: string,
     *  }> $criteria
     * @throws QueryBuilderException
     */
    public function setWhere(array $criteria): void
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
