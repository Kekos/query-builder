<?php

declare(strict_types=1);

namespace QueryBuilder;

class MySqlAdapter implements AdapterInterface
{
    public function getSanitizer(): string
    {
        return '`';
    }
}
