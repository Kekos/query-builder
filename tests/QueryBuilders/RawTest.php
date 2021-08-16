<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\QueryBuilders\Raw;

class RawTest extends TestCase
{
    public function testGetParams(): void
    {
        $sql = 'SELECT';
        $params = [42, 'bar'];

        $raw = new Raw($sql, $params);

        $this->assertEquals($params, $raw->getParams());
    }

    public function testToString(): void
    {
        $sql = 'SELECT';
        $params = [];

        $raw = new Raw($sql, $params);

        $this->assertEquals($sql, (string) $raw);
    }
}
