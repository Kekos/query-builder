<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Raw;
use QueryBuilder\QueryBuilders\Select;

class SelectTest extends TestCase
{
    private Select $select;

    protected function setUp(): void
    {
        parent::setUp();

        $this->select = new Select('foo', new MySqlAdapter());
    }

    public function testColumnsSingle(): void
    {
        $this->select->columns('foo');
        $this->assertEquals(['foo'], $this->select->getColumns());
    }

    public function testColumnsMulti(): void
    {
        $this->select->columns(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $this->select->getColumns());
    }

    public function testColumnsAlias(): void
    {
        $this->select->columns(['x' => 'foo']);
        $this->assertEquals(['x' => 'foo'], $this->select->getColumns());
    }

    public function testSetColumnsReplaces(): void
    {
        $expected = ['id' => 'foo'];
        $this->select->columns(['x' => 'foo']);

        $this->select->setColumns($expected);

        $this->assertEquals($expected, $this->select->getColumns());
    }

    public function testSetJoinsReplaces(): void
    {
        $this->select->join('t', 'id', '=', 'id');

        $this->select->setJoins([]);

        $this->assertEmpty($this->select->getJoins());
    }

    public function testGroupbySingle(): void
    {
        $this->select->groupby('foo');
        $this->assertEquals(['`foo`'], $this->select->getGroupBy());
    }

    public function testGroupbyMulti(): void
    {
        $this->select->groupby(['foo', 'bar']);
        $this->assertEquals(['`foo`', '`bar`'], $this->select->getGroupBy());
    }

    public function testSetGroupByReplaces(): void
    {
        $this->select->groupby('foo');

        $this->select->setGroupBy([]);

        $this->assertEmpty($this->select->getGroupBy());
    }

    public function testOrderbySingle(): void
    {
        $this->select->orderby('foo');
        $this->assertEquals(['`foo` ASC'], $this->select->getOrderBy());
    }

    public function testOrderbySingleDir(): void
    {
        $this->select->orderby('foo', 'DESC');
        $this->assertEquals(['`foo` DESC'], $this->select->getOrderBy());
    }

    public function testOrderbyInvalidDirThrows(): void
    {
        $this->expectException('QueryBuilder\\QueryBuilderException');
        $this->select->orderby('foo', 'BASC');
    }

    public function testOrderbyMulti(): void
    {
        $this->select->orderby(['foo', 'bar' => 'DESC']);
        $this->assertEquals(['`foo` ASC', '`bar` DESC'], $this->select->getOrderBy());
    }

    public function testSetOrderByReplaces(): void
    {
        $this->select->orderby('foo', 'DESC');

        $this->select->setOrderBy([]);

        $this->assertEmpty($this->select->getOrderBy());
    }

    public function testCloneWhereHaving(): void
    {
        $cloned_select = clone $this->select;
        $cloned_select->where('foo', '=', 42);
        $cloned_select->having('bar', '=', 1337);

        $this->assertEmpty($this->select->getWhere());
        $this->assertEmpty($this->select->getHaving());
    }

    public function testLimit(): void
    {
        $this->select->limit(10);
        $this->assertEquals(10, $this->select->getLimitRowCount());
    }

    public function testLimitOffset(): void
    {
        $this->select->limit(10, 20);
        $this->assertEquals(20, $this->select->getLimitOffset());
    }

    public function testToSqlSimple(): void
    {
        $expected = new Raw("SELECT *\n\tFROM `foo`\n", []);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlAlias(): void
    {
        $expected = new Raw("SELECT *\n\tFROM `foo` AS `f`\n", []);

        $select = new Select(['foo', 'f'], new MySqlAdapter());

        $this->assertEquals($expected, $select->toSql());
    }

    public function testToSqlColumns(): void
    {
        $expected = new Raw("SELECT `bar` AS `foo`\n\tFROM `foo`\n", []);

        $this->select->columns([
            'foo' => 'bar',
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlColumnsSubquery(): void
    {
        $subquery = '(SELECT bar FROM baz WHERE baz.id = 42)';

        $expected = new Raw("SELECT " . $subquery . " AS `foo`\n\tFROM `foo`\n", []);

        $this->select->columns([
            'foo' => new Raw($subquery),
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlColumnsSubqueryParams(): void
    {
        $subquery = '(SELECT bar FROM baz WHERE baz.id = ?)';

        $expected = new Raw("SELECT " . $subquery . " AS `foo`\n\tFROM `foo`\n", [42]);

        $this->select->columns([
            'foo' => new Raw($subquery, [42]),
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlColumnsMulti(): void
    {
        $expected = new Raw("SELECT `bar` AS `foo`, `baz`\n\tFROM `foo`\n", []);

        $this->select->columns([
            'foo' => 'bar',
            'baz',
        ]);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlJoin(): void
    {
        $expected = new Raw(
            "SELECT *\n\tFROM `foo`\n\tINNER JOIN `bar` ON `bar`.`foo_id` = `foo`.`id`\n",
            [],
        );

        $this->select->join('bar', new Raw("`bar`.`foo_id` = `foo`.`id`"));

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlJoinOn(): void
    {
        $expected = new Raw(
            "SELECT *\n\tFROM `foo`\n\tINNER JOIN `bar` ON `bar`.`foo_id` = `foo`.`id`\n",
            [],
        );

        $this->select->joinOn('bar', 'bar.foo_id', 'foo.id');

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlJoinParams(): void
    {
        $expected = new Raw(
            "SELECT *\n\tFROM `foo`\n\tINNER JOIN `bar` ON `id` = ?\n",
            [42],
        );

        $this->select->join('bar', 'id', '=', 42);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlWhere(): void
    {
        $expected = new Raw(
            "SELECT *\n\tFROM `foo`\n\tWHERE `bar` = ?\n",
            [42],
        );

        $this->select->where('bar', '=', 42);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlGroupBy(): void
    {
        $expected = new Raw("SELECT *\n\tFROM `foo`\n\tGROUP BY `bar`, `boo`\n", []);

        $this->select->groupby(['bar', 'boo']);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlHaving(): void
    {
        $expected = new Raw("SELECT *\n\tFROM `foo`\n\tHAVING `bar` = ?\n", [42]);

        $this->select->having('bar', '=', 42);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlOrderBy(): void
    {
        $expected = new Raw(
            "SELECT *\n\tFROM `foo`\n\tORDER BY `bar` ASC, `boo` DESC\n",
            [],
        );

        $this->select->orderby(['bar' => 'ASC', 'boo' => 'DESC']);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlLimit(): void
    {
        $expected = new Raw("SELECT *\n\tFROM `foo`\n\tLIMIT ?, ?\n", [20, 10]);

        $this->select->limit(10, 20);

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlCombined(): void
    {
        $expected = new Raw(
            "SELECT *\n\tFROM `foo`\n\t"
            . "WHERE `bar` = ?\n\t"
            . "GROUP BY `bar`, `boo`\n\t"
            . "HAVING `bar` = ?\n\t"
            . "ORDER BY `bar` ASC, `boo` DESC\n\t"
            . "LIMIT ?, ?\n",
            [42, 42, 20, 10],
        );

        $this->select
            ->where('bar', '=', 42)
            ->groupby(['bar', 'boo'])
            ->having('bar', '=', 42)
            ->orderby(['bar' => 'ASC', 'boo' => 'DESC'])
            ->limit(10, 20)
        ;

        $this->assertEquals($expected, $this->select->toSql());
    }

    public function testToSqlSubquery(): void
    {
        $subquery = 'SELECT bar FROM baz WHERE baz.id = ?';

        $expected = new Raw("SELECT *\n\tFROM (" . $subquery . ") AS `foo`\n", [42]);

        $select = new Select(new Raw($subquery, [42]), new MySqlAdapter());
        $select->alias('foo');

        $this->assertEquals($expected, $select->toSql());
    }

    public function testToSqlSubqueryDirectAlias(): void
    {
        $subquery = 'SELECT bar FROM baz WHERE baz.id = ?';

        $expected = new Raw("SELECT *\n\tFROM (" . $subquery . ") AS `foo`\n", [42]);

        $select = new Select(
            [new Raw($subquery, [42]), 'foo'],
            new MySqlAdapter(),
        );

        $this->assertEquals($expected, $select->toSql());
    }

    public function testToSqlSubqueryRaw(): void
    {
        $adapter = new MySqlAdapter();
        $subquery = new Select('baz', $adapter);
        $subquery
            ->columns('bar')
            ->where('baz.id', '=', 42)
        ;

        $expected = new Raw(
            "SELECT *\n\tFROM (SELECT `bar`\n\tFROM `baz`\n\tWHERE `baz`.`id` = ?\n) AS `foo`\n",
            [42],
        );

        $select = new Select($subquery->toSql(), $adapter);
        $select->alias('foo');

        $this->assertEquals($expected, $select->toSql());
    }
}
