<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\CriteriaBuilder;
use QueryBuilder\QueryBuilders\Raw;

class CriteriaBuilderTest extends TestCase
{
    /** @var CriteriaBuilder */
    private $criteria_instance;

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

    public function testToSqlWhereAnd(): void
    {
        $expected = new Raw('`foo` = ? AND `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->where('baz', '<', 5);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereAndNot(): void
    {
        $expected = new Raw('`foo` = ? AND NOT `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereNot('baz', '<', 5);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOr(): void
    {
        $expected = new Raw('`foo` = ? OR `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOr('baz', '<', 5);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOrNot(): void
    {
        $expected = new Raw('`foo` = ? OR NOT `baz` < ?', ['bar', 5]);

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOrNot('baz', '<', 5);

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
                    ->whereOr('baz', '<', 42);
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
}
