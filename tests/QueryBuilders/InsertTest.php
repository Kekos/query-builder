<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Insert;
use QueryBuilder\QueryBuilders\Raw;

class InsertTest extends TestCase
{
    /** @var Insert */
    private $insert;

    protected function setUp(): void
    {
        parent::setUp();

        $this->insert = new Insert('foo_join', new MySqlAdapter());
    }

    public function testToSql(): void
    {
        $expected = new Raw(
            "INSERT INTO `foo_join` (`foo`, `baz`, `boo`)\n\tVALUES (?, ?, ?)",
            ['bar', 42, null]
        );

        $this->insert->values([
            'foo' => 'bar',
            'baz' => 42,
            'boo' => null,
        ]);

        $this->assertEquals($expected, $this->insert->toSql());
    }
}
