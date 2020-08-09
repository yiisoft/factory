<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definitions;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Di\Container;
use Yiisoft\Factory\Definitions\DynamicReference;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;

class DynamicReferenceTest extends TestCase
{
    public function createContainer(): ContainerInterface
    {
        return new Container([
            EngineInterface::class => EngineMarkOne::class,
        ]);
    }

    public function testString(): void
    {
        $ref = DynamicReference::to(EngineInterface::class);
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createContainer()));
    }

    public function testClosure(): void
    {
        $ref = DynamicReference::to(function (ContainerInterface $container) {
            return $container->get(EngineInterface::class);
        });
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createContainer()));
    }

    public function testCallable(): void
    {
        $ref = DynamicReference::to([static::class, 'callableDefinition']);
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createContainer()));
    }

    public static function callableDefinition(ContainerInterface $container)
    {
        return $container->get(EngineInterface::class);
    }

    public function testFullDefinition(): void
    {
        $ref = DynamicReference::to([
            '__class' => EngineMarkOne::class,
        ]);
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createContainer()));
    }
}
