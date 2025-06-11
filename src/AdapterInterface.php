<?php

declare(strict_types=1);

namespace QueryBuilder;

interface AdapterInterface
{
    public function getSanitizer(): string;
}
