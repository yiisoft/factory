<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definitions;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\Reference;
use Yiisoft\Factory\Exception\InvalidConfigException;
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
        $this->expectException(InvalidConfigException::class);
        Reference::to(['class' => EngineInterface::class]);
    }
}
