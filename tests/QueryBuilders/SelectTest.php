<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Raw;
use QueryBuilder\QueryBuilders\Select;

class SelectTest extends TestCase
{
    /** @var Select */
    private $select;

    protected function setUp()
    {
        parent::setUp();

        $this->select = new Select('foo', new MySqlAdapter());
    }

    public function testColumnsSingle()
    {
        $this->select->columns('foo');
        $this->assertAttributeEquals(['foo'], 'columns', $this->select);
    }

    public function testColumnsMulti()
    {
        $this->select->columns(['foo', 'bar']);
        $this->assertAttributeEquals(['foo', 'bar'], 'columns', $this->select);
    }

    public function testColumnsAlias()
    {
        $this->select->columns(['x' => 'foo']);
        $this->assertAttributeEquals(['x' => 'foo'], 'columns', $this->select);
    }

    public function testGroupbySingle()
    {
        $this->select->groupby('foo');
        $this->assertAttributeEquals(['`foo`'], 'group_by', $this->select);
    }

    public function testGroupbyMulti()
    {
        $this->select->groupby(['foo', 'bar']);
        $this->assertAttributeEquals(['`foo`', '`bar`'], 'group_by', $this->select);
    }

    public function testOrderbySingle()
    {
        $this->select->orderby('foo');
        $this->assertAttributeEquals(['`foo` ASC'], 'order_by', $this->select);
    }

    public function testOrderbySingleDir()
    {
        $this->select->orderby('foo', 'DESC');
        $this->assertAttributeEquals(['`foo` DESC'], 'order_by', $this->select);
    }

    public function testOrderbyInvalidDirThrows()
    {
        $this->expectException('QueryBuilder\\QueryBuilderException');
        $this->select->orderby('foo', 'BASC');
    }

    public function testOrderbyMulti()
    {
        $this->select->orderby(['foo', 'bar' => 'DESC']);
        $this->assertAttributeEquals(['`foo` ASC', '`bar` DESC'], 'order_by', $this->select);
    }

    public function testLimit()
    {
        $this->select->limit(10);
        $this->assertAttributeEquals(10, 'limit_row_count', $this->select);
    }

    public function testLimitOffset()
    {
        $this->select->limit(10, 20);
        $this->assertAttributeEquals(20, 'limit_offset', $this->select);
    }

    public function testLimitInvalidThrows()
    {
        $this->expectException('QueryBuilder\\QueryBuilderException');
        $this->select->limit('foo');
    }

    public function testToSqlSimple()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n",
            'params' => [],
        ];

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlAlias()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo` AS `f`\n",
            'params' => [],
        ];

        $select = new Select(['foo', 'f'], new MySqlAdapter());

        $this->assertEquals($expected, $select->toSql());
    }

    public function testToSqlColumns()
    {
        $expected = [
            'sql' => "SELECT `bar` AS `foo`\n\tFROM `foo`\n",
            'params' => [],
        ];

        $this->select->columns([
            'foo' => 'bar',
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlColumnsSubquery()
    {
        $subquery = '(SELECT bar FROM baz WHERE baz.id = 42)';

        $expected = [
            'sql' => "SELECT " . $subquery . " AS `foo`\n\tFROM `foo`\n",
            'params' => [],
        ];

        $this->select->columns([
            'foo' => new Raw($subquery),
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlColumnsSubqueryParams()
    {
        $subquery = '(SELECT bar FROM baz WHERE baz.id = ?)';

        $expected = [
            'sql' => "SELECT " . $subquery . " AS `foo`\n\tFROM `foo`\n",
            'params' => [42],
        ];

        $this->select->columns([
            'foo' => new Raw($subquery, [42]),
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlColumnsMulti()
    {
        $expected = [
            'sql' => "SELECT `bar` AS `foo`, `baz`\n\tFROM `foo`\n",
            'params' => [],
        ];

        $this->select->columns([
            'foo' => 'bar',
            'baz',
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlJoin()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\tINNER JOIN `bar` ON `bar`.`foo_id` = `foo`.`id`\n",
            'params' => [],
        ];

        $this->select->join('bar', new Raw("`bar`.`foo_id` = `foo`.`id`"));

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlJoinParams()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\tINNER JOIN `bar` ON `id` = ?\n",
            'params' => [42],
        ];

        $this->select->join('bar', 'id', '=', 42);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlWhere()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\tWHERE `bar` = ?\n",
            'params' => [42],
        ];

        $this->select->where('bar', '=', 42);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlGroupBy()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\tGROUP BY `bar`, `boo`\n",
            'params' => [],
        ];

        $this->select->groupby(['bar', 'boo']);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlHaving()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\tHAVING `bar` = ?\n",
            'params' => [42],
        ];

        $this->select->having('bar', '=', 42);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlOrderBy()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\tORDER BY `bar` ASC, `boo` DESC\n",
            'params' => [],
        ];

        $this->select->orderby(['bar' => 'ASC', 'boo' => 'DESC']);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlLimit()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\tLIMIT ?, ?\n",
            'params' => [20, 10],
        ];

        $this->select->limit(10, 20);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlCombined()
    {
        $expected = [
            'sql' => "SELECT *\n\tFROM `foo`\n\t"
                . "WHERE `bar` = ?\n\t"
                . "GROUP BY `bar`, `boo`\n\t"
                . "HAVING `bar` = ?\n\t"
                . "ORDER BY `bar` ASC, `boo` DESC\n\t"
                . "LIMIT ?, ?\n",
            'params' => [42, 42, 20, 10],
        ];

        $this->select
            ->where('bar', '=', 42)
            ->groupby(['bar', 'boo'])
            ->having('bar', '=', 42)
            ->orderby(['bar' => 'ASC', 'boo' => 'DESC'])
            ->limit(10, 20);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlSubquery()
    {
        $subquery = 'SELECT bar FROM baz WHERE baz.id = ?';

        $expected = [
            'sql' => "SELECT *\n\tFROM (" . $subquery . ") AS `foo`\n",
            'params' => [42],
        ];

        $select = new Select(new Raw($subquery, [42]), new MySqlAdapter());
        $select->alias('foo');

        $this->assertEquals($expected, $select->toSql());
    }

    public function testToSqlSubqueryDirectAlias()
    {
        $subquery = 'SELECT bar FROM baz WHERE baz.id = ?';

        $expected = [
            'sql' => "SELECT *\n\tFROM (" . $subquery . ") AS `foo`\n",
            'params' => [42],
        ];

        $select = new Select(
            [new Raw($subquery, [42]), 'foo'],
            new MySqlAdapter()
        );

        $this->assertEquals($expected, $select->toSql());
    }

    public function testToSqlSubqueryRaw()
    {
        $adapter = new MySqlAdapter();
        $subquery = new Select('baz', $adapter);
        $subquery
            ->columns('bar')
            ->where('baz.id', '=', 42);

        $expected = [
            'sql' => "SELECT *\n\tFROM (SELECT `bar`\n\tFROM `baz`\n\tWHERE `baz`.`id` = ?\n) AS `foo`\n",
            'params' => [42],
        ];

        $select = new Select($subquery->toSql(), $adapter);
        $select->alias('foo');

        $this->assertEquals($expected, $select->toSql());
    }
}
