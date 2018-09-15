<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\CriteriaBuilder;
use QueryBuilder\QueryBuilders\Raw;

class CriteriaBuilderTest extends TestCase
{
    /** @var CriteriaBuilder */
    private $criteria_instance;

    private static $methods = [
        'where',
        'whereNot',
        'whereOr',
        'whereOrNot',
    ];

    private static $base_expected = [
        'key' => 'foo',
        'operator' => 'bar',
        'value' => 'baz',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->criteria_instance = new CriteriaBuilder(new MySqlAdapter());
    }

    public function testMethodsReturnsSameInstance()
    {
        foreach (self::$methods as $method) {
            $return_val = call_user_func([$this->criteria_instance, $method], 1, 2, 3, 4);
            $this->assertEquals($this->criteria_instance, $return_val);
        }
    }

    public function testWhereAddsCriteria()
    {
        $expected = self::$base_expected;
        $expected['joiner'] = 'boo';

        call_user_func_array([$this->criteria_instance, 'where'], $expected);

        $this->assertAttributeEquals([$expected], 'statements', $this->criteria_instance);
    }

    public function testWhereNotAddsCriteria()
    {
        $expected = self::$base_expected;

        call_user_func_array([$this->criteria_instance, 'whereNot'], $expected);

        $expected['joiner'] = 'AND NOT';

        $this->assertAttributeEquals([$expected], 'statements', $this->criteria_instance);
    }

    public function testWhereOrAddsCriteria()
    {
        $expected = self::$base_expected;

        call_user_func_array([$this->criteria_instance, 'whereOr'], $expected);

        $expected['joiner'] = 'OR';

        $this->assertAttributeEquals([$expected], 'statements', $this->criteria_instance);
    }

    public function testWhereOrNotAddsCriteria()
    {
        $expected = self::$base_expected;

        call_user_func_array([$this->criteria_instance, 'whereOrNot'], $expected);

        $expected['joiner'] = 'OR NOT';

        $this->assertAttributeEquals([$expected], 'statements', $this->criteria_instance);
    }

    public function testToSqlWhereSimple()
    {
        $expected = [
            'sql' => '`foo` = ? ',
            'params' => ['bar'],
        ];

        $this->criteria_instance->where('foo', '=', 'bar');

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereBetween()
    {
        $between = [20, 40];
        $expected = [
            'sql' => '`foo` BETWEEN ? AND ? ',
            'params' => $between,
        ];

        $this->criteria_instance->where('foo', 'BETWEEN', $between);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereNull()
    {
        $expected = [
            'sql' => '`foo` IS NULL ',
            'params' => [],
        ];

        $this->criteria_instance->where('foo', '=', null);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereAnd()
    {
        $expected = [
            'sql' => '`foo` = ? AND `baz` < ? ',
            'params' => ['bar', 5],
        ];

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->where('baz', '<', 5);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereAndNot()
    {
        $expected = [
            'sql' => '`foo` = ? AND NOT `baz` < ? ',
            'params' => ['bar', 5],
        ];

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereNot('baz', '<', 5);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOr()
    {
        $expected = [
            'sql' => '`foo` = ? OR `baz` < ? ',
            'params' => ['bar', 5],
        ];

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOr('baz', '<', 5);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereOrNot()
    {
        $expected = [
            'sql' => '`foo` = ? OR NOT `baz` < ? ',
            'params' => ['bar', 5],
        ];

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->whereOrNot('baz', '<', 5);

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereClosure()
    {
        $expected = [
            'sql' => '`foo` = ? AND (`boo` >= ? OR `baz` < ? ) ',
            'params' => ['bar', 5, 42],
        ];

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->where(function (CriteriaBuilder $cb) {
                $cb
                    ->where('boo', '>=', 5)
                    ->whereOr('baz', '<', 42);
            })
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }

    public function testToSqlWhereRaw()
    {
        $raw_sql = "(SELECT * FROM table2 WHERE moo > ?)";
        $expected = [
            'sql' => '`foo` = ? AND ' . $raw_sql . ' ',
            'params' => ['bar', 42],
        ];

        $this->criteria_instance
            ->where('foo', '=', 'bar')
            ->where(new Raw($raw_sql, [42]))
        ;

        $this->assertEquals($expected, $this->criteria_instance->toSql());
    }
}