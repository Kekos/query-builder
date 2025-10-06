<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Delete;
use QueryBuilder\QueryBuilders\Raw;

class DeleteTest extends TestCase
{
    private Delete $delete;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delete = new Delete('foo_join', new MySqlAdapter());
    }

    public function testCloneWhere(): void
    {
        $where_test1 = [
            'key' => 'test1',
            'operator' => '=',
            'value' => 42,
            'joiner' => 'AND',
        ];
        $where_foo = [
            'key' => 'foo',
            'operator' => '=',
            'value' => 42,
            'joiner' => 'AND',
        ];

        $this->delete->where($where_test1['key'], '=', $where_test1['value']);

        $cloned_delete = clone $this->delete;
        $cloned_delete->where($where_foo['key'], '=', $where_foo['value']);

        $this->assertEquals([$where_test1], $this->delete->getWhere());
        $this->assertEquals([$where_test1, $where_foo], $cloned_delete->getWhere());
    }

    public function testToSql(): void
    {
        $expected = new Raw("DELETE FROM `foo_join`\n\tWHERE `id` = ?", [42]);

        $this->delete->where('id', '=', 42);

        $this->assertEquals($expected, $this->delete->toSql());
    }
}
