<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\Delete;
use QueryBuilder\QueryBuilders\Raw;

class DeleteTest extends TestCase
{
    /** @var Delete */
    private $delete;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delete = new Delete('foo_join', new MySqlAdapter());
    }

    public function testToSql(): void
    {
        $expected = new Raw("DELETE FROM `foo_join`\n\tWHERE `id` = ?", [42]);

        $this->delete->where('id', '=', 42);

        $this->assertEquals($expected, $this->delete->toSql());
    }
}
