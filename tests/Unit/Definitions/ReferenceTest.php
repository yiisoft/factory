<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definitions;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
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
        Reference::to(['__class' => EngineInterface::class]);
    }
}
