<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\CriteriaBase;
use RuntimeException;

class CriteriaBaseTest extends TestCase
{
    /** @var CriteriaBase */
    private $criteria_instance;

    /** @var array<int, string> */
    private static array $methods = [
        'where',
        'whereNot',
        'whereOr',
        'whereOrNot',
    ];


    protected function setUp(): void
    {
        parent::setUp();

        $this->criteria_instance = $this->getMockForAbstractClass(
            CriteriaBase::class,
            [
                'test',
                new MySqlAdapter(),
            ],
        );
    }

    public function testMethodsReturnsSameInstance(): void
    {
        foreach (self::$methods as $method) {
            $callable = [$this->criteria_instance, $method];

            if (!is_callable($callable)) {
                throw new RuntimeException($method . '() is not callable on ' . CriteriaBase::class);
            }

            $return_val = call_user_func($callable, 1, 2, 3, 4);
            $this->assertEquals($this->criteria_instance, $return_val);
        }
    }

    public function testWhereAddsCriteria(): void
    {
        $expected = [
            'key' => $key =  'foo',
            'operator' => $operator =  'bar',
            'value' => $value =  'baz',
            'joiner' => $joiner = 'boo',
        ];

        $this->criteria_instance->where($key, $operator, $value, $joiner);

        $this->assertEquals([$expected], $this->criteria_instance->getWhere());
    }

    public function testWhereNotAddsCriteria(): void
    {
        $expected = [
            'key' => $key =  'foo',
            'operator' => $operator =  'bar',
            'value' => $value =  'baz',
        ];

        $this->criteria_instance->whereNot($key, $operator, $value);

        $expected['joiner'] = 'AND NOT';

        $this->assertEquals([$expected], $this->criteria_instance->getWhere());
    }

    public function testWhereOrAddsCriteria(): void
    {
        $expected = [
            'key' => $key =  'foo',
            'operator' => $operator =  'bar',
            'value' => $value =  'baz',
        ];

        $this->criteria_instance->whereOr($key, $operator, $value);

        $expected['joiner'] = 'OR';

        $this->assertEquals([$expected], $this->criteria_instance->getWhere());
    }

    public function testWhereOrNotAddsCriteria(): void
    {
        $expected = [
            'key' => $key =  'foo',
            'operator' => $operator =  'bar',
            'value' => $value =  'baz',
        ];

        $this->criteria_instance->whereOrNot($key, $operator, $value);

        $expected['joiner'] = 'OR NOT';

        $this->assertEquals([$expected], $this->criteria_instance->getWhere());
    }

    public function testSetWhereReplaces(): void
    {
        $expected = [
            [
                'key' => 'foo',
                'operator' => 'bar',
                'value' => 'baz',
                'joiner' => 'AND',
            ],
        ];

        $this->criteria_instance->where('id', '=', 1);
        $this->criteria_instance->setWhere($expected);

        $this->assertEquals($expected, $this->criteria_instance->getWhere());
    }

    public function testSetWhereThrowsOnInvalidArrayShape(): void
    {
        $this->expectExceptionMessage('Missing the required key `key` in criterion array index 0:');

        // @phpstan-ignore argument.type
        $this->criteria_instance->setWhere([[
            'foo' => 'bar',
        ]]);
    }

    public function testSetWhereThrowsOnInvalidArrayShapeAllowNull(): void
    {
        $expected = [
            [
                'key' => 'id',
                'operator' => '=',
                'value' => null,
                'joiner' => 'AND',
            ],
        ];

        $this->criteria_instance->setWhere($expected);

        $this->assertEquals($expected, $this->criteria_instance->getWhere());
    }
}
