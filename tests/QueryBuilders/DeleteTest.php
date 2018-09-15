<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Delete;

class DeleteTest extends TestCase
{
    /** @var Delete */
    private $delete;

    protected function setUp()
    {
        parent::setUp();

        $this->delete = new Delete('foo_join', new MySqlAdapter());
    }

    public function testToSql()
    {
        $expected = [
            'sql' => "DELETE FROM `foo_join`\n\tWHERE `id` = ? ",
            'params' => [42],
        ];

        $this->delete->where('id', '=', 42);

        $this->assertEquals($expected, $this->delete->toSql());
    }
}
