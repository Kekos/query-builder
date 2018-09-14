<?php
namespace QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;

class MySqlAdapterTest extends TestCase
{
    public function testReturnsMySqlSanitizer()
    {
        $adapter = new MySqlAdapter();
        $this->assertEquals('`', $adapter->getSanitizer());
    }
}
