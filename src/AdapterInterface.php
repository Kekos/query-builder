<?php

declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * AdapterInterface
 *
 * @version 1.0
 * @date 2015-12-06
 */

namespace QueryBuilder;

interface AdapterInterface
{
    public function getSanitizer(): string;
}
