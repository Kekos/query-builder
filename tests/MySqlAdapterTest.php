<?php

declare(strict_types=1);

namespace QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;

class MySqlAdapterTest extends TestCase
{
    public function testReturnsMySqlSanitizer(): void
    {
        $adapter = new MySqlAdapter();
        $this->assertEquals('`', $adapter->getSanitizer());
    }
}
