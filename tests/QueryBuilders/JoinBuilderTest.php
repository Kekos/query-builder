<?php declare(strict_types=1);
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\JoinBuilder;
use QueryBuilder\QueryBuilders\Raw;

class JoinBuilderTest extends TestCase
{
    /** @var JoinBuilder */
    private $join_instance;

    private static $join_type = 'INNER';

    private static $methods = [
        'on',
        'onOr',
    ];

    private static $base_expected = [
        'key' => 'foo',
        'operator' => 'bar',
        'value' => 'baz',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->join_instance = new JoinBuilder(new MySqlAdapter(), [], 'foo_join', self::$join_type);
    }

    public function testMethodsReturnsSameInstance(): void
    {
        foreach (self::$methods as $method) {
            $return_val = call_user_func([$this->join_instance, $method], 1, 2, 3, 4);
            $this->assertEquals($this->join_instance, $return_val);
        }
    }

    public function testOnAddsCriteria(): void
    {
        $expected = self::$base_expected;
        $expected['joiner'] = 'boo';

        call_user_func_array([$this->join_instance, 'on'], $expected);

        $this->assertAttributeEquals([$expected], 'statements', $this->join_instance);
    }

    public function testOnOrAddsCriteria(): void
    {
        $expected = self::$base_expected;

        call_user_func_array([$this->join_instance, 'onOr'], $expected);

        $expected['joiner'] = 'OR';

        $this->assertAttributeEquals([$expected], 'statements', $this->join_instance);
    }

    public function testToSqlSimple(): void
    {
        $expected = [
            'sql' => 'INNER JOIN `foo_join` ON `bar` = ?',
            'params' => [42],
        ];

        $this->join_instance->on('bar', '=', 42);

        $this->assertEquals($expected, $this->join_instance->toSql());
    }

    public function testToSqlAlias(): void
    {
        $expected = [
            'sql' => 'INNER JOIN `foo_join` AS `fj` ON `bar` = ?',
            'params' => [42],
        ];

        $join_instance = new JoinBuilder(
            new MySqlAdapter(),
            [],
            ['foo_join', 'fj'],
            'INNER'
        );

        $join_instance->on('bar', '=', 42);

        $this->assertEquals($expected, $join_instance->toSql());
    }

    public function testToSqlOnRaw(): void
    {
        $raw_sql = "`bar` = `baz`";
        $expected = [
            'sql' => 'INNER JOIN `foo_join` ON ' . $raw_sql,
            'params' => [],
        ];

        $this->join_instance->where(new Raw($raw_sql));

        $this->assertEquals($expected, $this->join_instance->toSql());
    }
}
