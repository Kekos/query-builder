<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Insert;

class InsertTest extends TestCase
{
    /** @var Insert */
    private $insert;

    protected function setUp()
    {
        parent::setUp();

        $this->insert = new Insert('foo_join', new MySqlAdapter());
    }

    public function testToSql()
    {
        $expected = [
            'sql' => "INSERT INTO `foo_join` (`foo`, `baz`, `boo`)\n\tVALUES (?, ?, ?)",
            'params' => ['bar', 42, null],
        ];

        $this->insert->values([
            'foo' => 'bar',
            'baz' => 42,
            'boo' => null,
        ]);

        $this->assertEquals($expected, $this->insert->toSql());
    }
}
