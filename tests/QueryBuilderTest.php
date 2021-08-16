<?php

declare(strict_types=1);

namespace QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    public function testRaw(): void
    {
        $sql = 'SELECT ?';
        $params = [42];
        $return = QueryBuilder::raw($sql, $params);

        $this->assertEquals($sql, (string) $return);
        $this->assertEquals($params, $return->getParams());
    }

    public function testSanitizeFieldStar(): void
    {
        $return = QueryBuilder::sanitizeField('*', '`');
        $this->assertEquals('*', $return);
    }

    public function testSanitizeFieldStarAlias(): void
    {
        $return = QueryBuilder::sanitizeField('foo.*', '`');
        $this->assertEquals('`foo`.*', $return);
    }

    public function testSanitizeFieldAlias(): void
    {
        $return = QueryBuilder::sanitizeField('foo.bar', '`');
        $this->assertEquals('`foo`.`bar`', $return);
    }
}
