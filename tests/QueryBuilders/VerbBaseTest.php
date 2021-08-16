<?php
namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\VerbBase;

class VerbBaseTest extends TestCase
{
    /** @var VerbBase */
    private $verb_base;

    protected function setUp()
    {
        parent::setUp();

        $this->verb_base = $this->getMockForAbstractClass(
            'QueryBuilder\\QueryBuilders\\VerbBase',
            [
                'test',
                new MySqlAdapter(),
            ]
        );
    }

    public function testAliasReturnsSameInstance()
    {
        $this->assertEquals($this->verb_base, $this->verb_base->alias('alias'));
    }

    public function testAlias()
    {
        $this->verb_base->alias('alias');
        $this->assertEquals(['test', 'alias'], $this->verb_base->getTableName());
        $this->assertEquals('alias', $this->verb_base->getAlias());
    }

    public function testAliasReplace()
    {
        /** @var VerbBase $verb_base */
        $verb_base = $this->getMockForAbstractClass(
            'QueryBuilder\\QueryBuilders\\VerbBase',
            [
                ['test', 'foo'],
                new MySqlAdapter(),
            ]
        );

        $verb_base->alias('alias');
        $this->assertEquals(['test', 'alias'], $verb_base->getTableName());
        $this->assertEquals('alias', $verb_base->getAlias());
    }
}
