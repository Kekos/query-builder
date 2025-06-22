<?php

declare(strict_types=1);

namespace QueryBuilder\QueryBuilders;

use QueryBuilder\AdapterInterface;
use QueryBuilder\QueryBuilder;

use function is_array;

class Delete extends VerbBase
{
    use HasWhere;

    private CriteriaBuilder $where;

    /**
     * @inheritDoc
     */
    public function __construct(string|array|Raw $table_name, AdapterInterface $adapter)
    {
        parent::__construct($table_name, $adapter);

        $this->where = new CriteriaBuilder($adapter);
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
        $sql = "DELETE FROM ";
        $params = [];

        // Table name
        if (is_array($this->table_name)) {
            [$table_name, $alias] = $this->table_name;
            $sql .= $this->sanitizeField($table_name) . " AS " . $this->sanitizeField($alias);
        } else {
            $sql .= $this->sanitizeField($this->table_name);
        }

        // Where
        if (!$this->where->isEmpty()) {
            $where = $this->where->toSql();

            $sql .= "\n\tWHERE " . $where;
            $params = $where->getParams();
        }

        return new Raw($sql, $params);
    }
}
