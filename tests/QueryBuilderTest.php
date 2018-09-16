<?php declare(strict_types=1);
namespace QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    public function testSetAdapter(): void
    {
        $adapter = new MySqlAdapter();
        QueryBuilder::setAdapter($adapter);

        $this->assertAttributeEquals($adapter, 'adapter', 'QueryBuilder\\QueryBuilder');
    }

    public function testSelect(): void
    {
        $return = QueryBuilder::select('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Select', $return);
        $this->assertAttributeEquals('foo', 'table_name', $return);
    }

    public function testInsert(): void
    {
        $return = QueryBuilder::insert('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Insert', $return);
        $this->assertAttributeEquals('foo', 'table_name', $return);
    }

    public function testUpdate(): void
    {
        $return = QueryBuilder::update('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Update', $return);
        $this->assertAttributeEquals('foo', 'table_name', $return);
    }

    public function testDelete(): void
    {
        $return = QueryBuilder::delete('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Delete', $return);
        $this->assertAttributeEquals('foo', 'table_name', $return);
    }

    public function testRaw(): void
    {
        $sql = 'SELECT ?';
        $params = [42];
        $return = QueryBuilder::raw($sql, $params);

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Raw', $return);
        $this->assertAttributeEquals($sql, 'sql', $return);
        $this->assertAttributeEquals($params, 'params', $return);
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
