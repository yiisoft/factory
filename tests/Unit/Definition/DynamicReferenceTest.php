<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definition\DynamicReference;
use Yiisoft\Factory\DependencyResolver;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\TestHelper;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\SimpleContainer;

class DynamicReferenceTest extends TestCase
{
    public function createDependencyResolver(): DependencyResolver
    {
        $container = new SimpleContainer([
            ContainerInterface::class => &$container,
            EngineInterface::class => new EngineMarkOne(),
            Injector::class => &$injector,
        ]);
        $injector = new Injector($container);
        return TestHelper::createDependencyResolver($container);
    }

    public function testString(): void
    {
        $ref = DynamicReference::to(EngineInterface::class);
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createDependencyResolver()));
    }

    public function testClosure(): void
    {
        $ref = DynamicReference::to(
            fn (ContainerInterface $container) => $container->get(EngineInterface::class)
        );
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createDependencyResolver()));
    }

    public function testStaticClosure(): void
    {
        $ref = DynamicReference::to(
            static fn (ContainerInterface $container) => $container->get(EngineInterface::class)
        );
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createDependencyResolver()));
    }

    public function testCallable(): void
    {
        $ref = DynamicReference::to([static::class, 'callableDefinition']);
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createDependencyResolver()));
    }

    public static function callableDefinition(ContainerInterface $container)
    {
        return $container->get(EngineInterface::class);
    }

    public function testFullDefinition(): void
    {
        $ref = DynamicReference::to([
            'class' => EngineMarkOne::class,
        ]);
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createDependencyResolver()));
    }
}
