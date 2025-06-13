<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\QueryBuilder;

use function array_keys;
use function implode;
use function is_array;

class Insert extends VerbBase
{
    /**
     * @var array<string, ?scalar>
     */
    private array $values = [];

    /**
     * @param array<string, ?scalar> $values Column name as array key
     * @return $this
     */
    public function values(array $values): self
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
        $sql = "INSERT INTO ";
        $params = [];
        $placeholders = [];

        // Table name
        if (is_array($this->table_name)) {
            [$table_name, $alias] = $this->table_name;
            $sql .= $this->sanitizeField($table_name) . " AS " . $this->sanitizeField($alias);
        } else {
            $sql .= $this->sanitizeField($this->table_name);
        }

        // Columns
        $columns = array_keys($this->values);
        foreach ($columns as &$column) {
            $column = $this->sanitizeField($column);
        }

        $sql .= " (" . implode(", ", $columns) . ")\n";

        // Values
        $sql .= "\tVALUES (";
        foreach ($this->values as $value) {
            $params[] = $value;
            $placeholders[] = '?';
        }

        $sql .= implode(", ", $placeholders) . ")";

        return new Raw($sql, $params);
    }
}
