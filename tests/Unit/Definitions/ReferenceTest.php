<?php

namespace Yiisoft\Factory\Tests\Unit\Definitions;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\Factory\Tests\Support\EngineInterface;

/**
 * ReferenceTest contains tests for \yii\di\Reference
 */
class ReferenceTest extends TestCase
{
    public function testTo(): void
    {
        $ref = Reference::to(EngineInterface::class);
        $this->assertSame(EngineInterface::class, $ref->getId());
    }

    public function testInvalid(): void
    {
        $this->expectErrorMessage('$id should be string.');
        Reference::to(['__class' => EngineInterface::class]);
    }
}
