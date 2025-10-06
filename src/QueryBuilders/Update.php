<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\AdapterInterface;
use QueryBuilder\QueryBuilder;

use function array_merge;
use function implode;
use function is_array;

class Update extends VerbBase
{
    use HasWhere;

    private CriteriaBuilder $where;

    /**
     * @var array<string, scalar|Raw|null>
     */
    private array $values = [];

    /**
     * @inheritDoc
     */
    public function __construct(string|array|Raw $table_name, AdapterInterface $adapter)
    {
        parent::__construct($table_name, $adapter);

        $this->where = new CriteriaBuilder($adapter);
    }

    public function __clone()
    {
        $this->where = clone $this->where;
    }

    /**
     * @param array<string, scalar|Raw|null> $values Column name as array key
     * @return $this
     */
    public function set(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    private function sanitizeField(string|Raw $field): string
    {
        if ($field instanceof Raw) {
            return (string) $field;
        }

        return QueryBuilder::sanitizeField($field, $this->adapter->getSanitizer());
    }

    public function toSql(): Raw
    {
        $sql = "UPDATE ";
        $params = [];
        $placeholders = [];

        // Table name
        if (is_array($this->table_name)) {
            [$table_name, $alias] = $this->table_name;
            $sql .= $this->sanitizeField($table_name) . " AS " . $this->sanitizeField($alias);
        } else {
            $sql .= $this->sanitizeField($this->table_name);
        }

        // SET
        $sql .= "\n\tSET\n";
        foreach ($this->values as $column => $value) {
            if ($value instanceof Raw) {
                $params = array_merge($params, $value->getParams());
                $placeholders[] = "\t\t" . $this->sanitizeField($column) . " = " . $value;
            } else {
                $params[] = $value;
                $placeholders[] = "\t\t" . $this->sanitizeField($column) . " = ?";
            }
        }

        $sql .= implode(",\n", $placeholders) . "\n";

        // Where
        if (!$this->where->isEmpty()) {
            $where = $this->where->toSql();

            $sql .= "\tWHERE " . $where;
            $params = array_merge($params, $where->getParams());
        }

        return new Raw($sql, $params);
    }
}
