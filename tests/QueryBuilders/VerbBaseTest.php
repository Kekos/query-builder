<?php

declare(strict_types=1);

namespace QueryBuilder\Tests\QueryBuilders;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use QueryBuilder\MySqlAdapter;
use QueryBuilder\QueryBuilders\VerbBase;

class VerbBaseTest extends TestCase
{
    /** @var VerbBase */
    private $verb_base;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verb_base = $this->getMockForAbstractClass(
            VerbBase::class,
            [
                'test',
                new MySqlAdapter(),
            ]
        );
    }

    public function testAliasReturnsSameInstance(): void
    {
        $this->assertEquals($this->verb_base, $this->verb_base->alias('alias'));
    }

    public function testAlias(): void
    {
        $this->verb_base->alias('alias');
        $this->assertEquals(['test', 'alias'], $this->verb_base->getTableName());
        $this->assertEquals('alias', $this->verb_base->getAlias());
    }

    public function testAliasReplace(): void
    {
        /** @var VerbBase&MockObject $verb_base */
        $verb_base = $this->getMockForAbstractClass(
            VerbBase::class,
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
