<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\QueryBuilders\Raw;

class RawTest extends TestCase
{
    public function testConstructor()
    {
        $sql = 'SELECT';
        $params = [42];

        $raw = new Raw($sql, $params);

        $this->assertAttributeEquals($sql, 'sql', $raw);
    }

    public function testGetParams()
    {
        $sql = 'SELECT';
        $params = [42, 'bar'];

        $raw = new Raw($sql, $params);

        $this->assertEquals($params, $raw->getParams());
    }

    public function testToString()
    {
        $sql = 'SELECT';
        $params = [];

        $raw = new Raw($sql, $params);

        $this->assertEquals($sql, (string) $raw);
    }
}
