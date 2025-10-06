<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Raw;
use QueryBuilder\QueryBuilders\Update;

class UpdateTest extends TestCase
{
    /** @var Update */
    private $update;

    protected function setUp()
    {
        parent::setUp();

        $this->update = new Update('foo', new MySqlAdapter());
    }

    public function testCloneWhere()
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

        $this->update->where($where_test1['key'], '=', $where_test1['value']);

        $cloned_update = clone $this->update;
        $cloned_update->where($where_foo['key'], '=', $where_foo['value']);

        $this->assertEquals([$where_test1], $this->update->getWhere());
        $this->assertEquals([$where_test1, $where_foo], $cloned_update->getWhere());
    }

    public function testToSql()
    {
        $expected = [
            'sql' => "UPDATE `foo`\n\t"
                . "SET\n\t\t`foo` = ?,\n\t\t`baz` = ?,\n\t\t`boo` = ?\n",
            'params' => ['bar', 42, null],
        ];

        $this->update->set([
            'foo' => 'bar',
            'baz' => 42,
            'boo' => null,
        ]);

        $this->assertEquals($expected, $this->update->toSql());
    }

    public function testToSqlWithRaw()
    {
        $raw_sql = '(SELECT bar FROM b WHERE id = ?)';
        $expected = [
            'sql' => "UPDATE `foo`\n\t"
                . "SET\n\t\t`foo` = ?,\n\t\t`baz` = " . $raw_sql . "\n",
            'params' => ['bar', 42],
        ];

        $this->update->set([
            'foo' => 'bar',
            'baz' => new Raw($raw_sql, [42]),
        ]);

        $this->assertEquals($expected, $this->update->toSql());
    }

    public function testToSqlWhere()
    {
        $expected = [
            'sql' => "UPDATE `foo`\n\t"
                . "SET\n\t\t`foo` = ?\n\tWHERE `baz` = ? AND `boo` IS NULL",
            'params' => ['bar', 42],
        ];

        $this->update
            ->set([
                'foo' => 'bar',
            ])
            ->where('baz', '=', 42)
            ->where('boo', '=', null);

        $this->assertEquals($expected, $this->update->toSql());
    }
}
