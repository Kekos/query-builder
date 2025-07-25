<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\JoinBuilder;
use QueryBuilder\QueryBuilders\Raw;

class JoinBuilderTest extends TestCase
{
    private JoinBuilder $join_instance;

    private static string $join_type = 'INNER';

    protected function setUp(): void
    {
        parent::setUp();

        $this->join_instance = new JoinBuilder(new MySqlAdapter(), [], 'foo_join', self::$join_type);
    }

    public function testToSqlSimple(): void
    {
        $expected = new Raw('INNER JOIN `foo_join` ON `bar` = ?', [42]);

        $this->join_instance->on('bar', '=', 42);

        $this->assertEquals($expected, $this->join_instance->toSql());
    }

    public function testToSqlOr(): void
    {
        $expected = new Raw('INNER JOIN `foo_join` ON `bar` = ? OR `baz` = ?', [42, 43]);

        $this->join_instance->on('bar', '=', 42);
        $this->join_instance->onOr('baz', '=', 43);

        $this->assertEquals($expected, $this->join_instance->toSql());
    }

    public function testToSqlAlias(): void
    {
        $expected = new Raw('INNER JOIN `foo_join` AS `fj` ON `bar` = ?', [42]);

        $join_instance = new JoinBuilder(
            new MySqlAdapter(),
            [],
            ['foo_join', 'fj'],
            'INNER',
        );

        $join_instance->on('bar', '=', 42);

        $this->assertEquals($expected, $join_instance->toSql());
    }

    public function testToSqlOnRaw(): void
    {
        $raw_sql = "`bar` = `baz`";
        $expected = new Raw('INNER JOIN `foo_join` ON ' . $raw_sql, []);

        $this->join_instance->where(new Raw($raw_sql));

        $this->assertEquals($expected, $this->join_instance->toSql());
    }

    public function testToSqlOnRawSubSelect(): void
    {
        $expected = new Raw(
            'INNER JOIN (SELECT * FROM `bar_join` WHERE `id` = ?) AS `foo_join` ON `bar` = ?',
            [
                2,
                42,
            ],
        );

        $join_instance = new JoinBuilder(
            new MySqlAdapter(),
            [],
            new Raw('(SELECT * FROM `bar_join` WHERE `id` = ?) AS `foo_join`', [2]),
            self::$join_type,
        );
        $join_instance
            ->on('bar', '=', 42)
        ;

        $this->assertEquals($expected, $join_instance->toSql());
    }
}
