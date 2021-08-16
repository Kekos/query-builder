<?php
namespace QueryBuilder\Tests;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    public function testSetAdapter()
    {
        $adapter = new MySqlAdapter();
        QueryBuilder::setAdapter($adapter);

        $this->assertEquals($adapter, QueryBuilder::getAdapter());
    }

    public function testSelect()
    {
        $return = QueryBuilder::select('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Select', $return);
        $this->assertEquals('foo', $return->getTableName());
    }

    public function testInsert()
    {
        $return = QueryBuilder::insert('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Insert', $return);
        $this->assertEquals('foo', $return->getTableName());
    }

    public function testUpdate()
    {
        $return = QueryBuilder::update('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Update', $return);
        $this->assertEquals('foo', $return->getTableName());
    }

    public function testDelete()
    {
        $return = QueryBuilder::delete('foo');

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Delete', $return);
        $this->assertEquals('foo', $return->getTableName());
    }

    public function testRaw()
    {
        $sql = 'SELECT ?';
        $params = [42];
        $return = QueryBuilder::raw($sql, $params);

        $this->assertInstanceOf('QueryBuilder\\QueryBuilders\\Raw', $return);
        $this->assertAttributeEquals($sql, 'sql', $return);
        $this->assertAttributeEquals($params, 'params', $return);
    }

    public function testSanitizeFieldStar()
    {
        $return = QueryBuilder::sanitizeField('*', '`');
        $this->assertEquals('*', $return);
    }

    public function testSanitizeFieldStarAlias()
    {
        $return = QueryBuilder::sanitizeField('foo.*', '`');
        $this->assertEquals('`foo`.*', $return);
    }

    public function testSanitizeFieldAlias()
    {
        $return = QueryBuilder::sanitizeField('foo.bar', '`');
        $this->assertEquals('`foo`.`bar`', $return);
    }
}
