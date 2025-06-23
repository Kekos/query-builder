<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\CriteriaBuilder;
use QueryBuilder\QueryBuilders\Raw;

class CriteriaBuilderTest extends TestCase
{
    private CriteriaBuilder $criteria_instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->criteria_instance = new CriteriaBuilder(new MySqlAdapter());
    }

    public function testToSqlWhereSimple(): void
    {
        $expected = new Raw('`foo` = ?', ['bar']);

        $this->criteria_instance->where('foo', '=', 'bar');

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereBetween(): void
    {
        $between = [20, 40];
        $expected = new Raw('`foo` BETWEEN ? AND ?', $between);

        $this->criteria_instance->where('foo', 'BETWEEN', $between);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereIn(): void
    {
        $between = [20, 40, 50];
        $expected = new Raw('`foo` IN (?, ?, ?)', $between);

        $this->criteria_instance->where('foo', 'IN', $between);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereNull(): void
    {
        $expected = new Raw('`foo` IS NULL');

        $this->criteria_instance->where('foo', '=', null);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereIsNull(): void
    {
        $expected = new Raw('`foo` IS NULL', []);

        $this->criteria_instance->whereIsNull('foo');

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereColumnsEquals(): void
    {
        $expected = new Raw('`a`.`foo` = `b`.`bar`', []);

        $this->criteria_instance->whereColumnsEquals('a.foo', 'b.bar');

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereColumnsNotEquals(): void
    {
        $expected = new Raw('`a`.`foo` != `b`.`bar`', []);

        $this->criteria_instance->whereColumnsNotEquals('a.foo', 'b.bar');

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereIsNotNull(): void
    {
        $expected = new Raw('NOT `foo` IS NULL', []);

        $this->criteria_instance->whereIsNotNull('foo');

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereAnd(): void
    {
        $expected = new Raw('`foo` = ? AND `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->where('baz', '<', 5)
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereAndNot(): void
    {
        $expected = new Raw('`foo` = ? AND NOT `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereNot('baz', '<', 5)
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOr(): void
    {
        $expected = new Raw('`foo` = ? OR `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOr('baz', '<', 5)
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOrNot(): void
    {
        $expected = new Raw('`foo` = ? OR NOT `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOrNot('baz', '<', 5)
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOrIsNull(): void
    {
        $expected = new Raw('`foo` = ? OR `foo` IS NULL', ['bar']);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOrIsNull('foo')
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOrIsNotNull(): void
    {
        $expected = new Raw('`foo` = ? OR NOT `foo` IS NULL', ['bar']);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOrIsNotNull('foo')
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOrColumnsEquals(): void
    {
        $expected = new Raw('`foo` = ? OR `a`.`foo` = `b`.`bar`', ['bar']);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOrColumnsEquals('a.foo', 'b.bar')
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOrColumnsNotEquals(): void
    {
        $expected = new Raw('`foo` = ? OR `a`.`foo` != `b`.`bar`', ['bar']);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOrColumnsNotEquals('a.foo', 'b.bar')
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereClosure(): void
    {
        $expected = new Raw('`foo` = ? AND (`boo` >= ? OR `baz` < ?)', ['bar', 5, 42]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->where(function (CriteriaBuilder $cb): void {
                $cb
                    ->where('boo', '>=', 5)
                    ->whereOr('baz', '<', 42)
                ;
            })
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereRaw(): void
    {
        $raw_sql = "(SELECT * FROM table2 WHERE moo > ?)";
        $expected = new Raw('`foo` = ? AND ' . $raw_sql, ['bar', 42]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->where(new Raw($raw_sql, [42]))
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testSetStatementsReplaces(): void
    {
        $expected = [
            [
                'key' => 'foo',
                'operator' => 'bar',
                'value' => 'baz',
                'joiner' => 'AND',
            ],
        ];

        $this->criteria_instance->where('id', '=', 1);
        $this->criteria_instance->setStatements($expected);

        $this->assertEquals($expected, $this->criteria_instance->getStatements());
    }

    public function testSetStatementsThrowsOnInvalidArrayShape(): void
    {
        $this->expectExceptionMessage('Missing the required key `key` in criterion array index 0:');

        // @phpstan-ignore argument.type
        $this->criteria_instance->setStatements([[
            'foo' => 'bar',
        ]]);
    }

    public function testSetStatementsThrowsOnInvalidArrayShapeAllowNull(): void
    {
        $expected = [
            [
                'key' => 'id',
                'operator' => '=',
                'value' => null,
                'joiner' => 'AND',
            ],
        ];

        $this->criteria_instance->setStatements($expected);

        $this->assertEquals($expected, $this->criteria_instance->getStatements());
    }
}
