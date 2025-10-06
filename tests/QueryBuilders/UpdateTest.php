<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Raw;
use QueryBuilder\QueryBuilders\Update;

class UpdateTest extends TestCase
{
    private Update $update;

    protected function setUp(): void
    {
        parent::setUp();

        $this->update = new Update('foo', new MySqlAdapter());
    }

    public function testCloneWhere(): void
    {
        $cloned_update = clone $this->update;
        $cloned_update->where('foo', '=', 42);

        $this->assertEmpty($this->update->getWhere());
    }

    public function testToSql(): void
    {
        $expected = new Raw(
            "UPDATE `foo`\n\t"
            . "SET\n\t\t`foo` = ?,\n\t\t`baz` = ?,\n\t\t`boo` = ?\n",
            ['bar', 42, null],
        );

        $this->update->set([
            'foo' => 'bar',
            'baz' => 42,
            'boo' => null,
        ]);

        $this->assertEquals($expected, $this->update->toSql());
    }

    public function testToSqlWithRaw(): void
    {
        $raw_sql = '(SELECT bar FROM b WHERE id = ?)';
        $expected = new Raw(
            "UPDATE `foo`\n\t"
            . "SET\n\t\t`foo` = ?,\n\t\t`baz` = " . $raw_sql . "\n",
            ['bar', 42],
        );

        $this->update->set([
            'foo' => 'bar',
            'baz' => new Raw($raw_sql, [42]),
        ]);

        $this->assertEquals($expected, $this->update->toSql());
    }

    public function testToSqlWhere(): void
    {
        $expected = new Raw(
            "UPDATE `foo`\n\t"
            . "SET\n\t\t`foo` = ?\n\tWHERE `baz` = ? AND `boo` IS NULL",
            ['bar', 42],
        );

        $this->update
            ->set([
                'foo' => 'bar',
            ])
            ->where('baz', '=', 42)
            ->whereIsNull('boo')
        ;

        $this->assertEquals($expected, $this->update->toSql());
    }
}
