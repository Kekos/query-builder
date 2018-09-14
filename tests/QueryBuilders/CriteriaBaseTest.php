<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\CriteriaBase;

class CriteriaBaseTest extends TestCase
{
    /** @var CriteriaBase */
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

        $this->criteria_instance = $this->getMockForAbstractClass(
            'QueryBuilder\\QueryBuilders\\CriteriaBase',
            [
                'test',
                new MySqlAdapter(),
            ]
        );
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

        $this->assertAttributeEquals([$expected], 'where', $this->criteria_instance);
    }

    public function testWhereNotAddsCriteria()
    {
        $expected = self::$base_expected;

        call_user_func_array([$this->criteria_instance, 'whereNot'], $expected);

        $expected['joiner'] = 'AND NOT';

        $this->assertAttributeEquals([$expected], 'where', $this->criteria_instance);
    }

    public function testWhereOrAddsCriteria()
    {
        $expected = self::$base_expected;

        call_user_func_array([$this->criteria_instance, 'whereOr'], $expected);

        $expected['joiner'] = 'OR';

        $this->assertAttributeEquals([$expected], 'where', $this->criteria_instance);
    }

    public function testWhereOrNotAddsCriteria()
    {
        $expected = self::$base_expected;

        call_user_func_array([$this->criteria_instance, 'whereOrNot'], $expected);

        $expected['joiner'] = 'OR NOT';

        $this->assertAttributeEquals([$expected], 'where', $this->criteria_instance);
    }
}
