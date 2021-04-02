<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definitions;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\DynamicReference;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;
use Yiisoft\Test\Support\Container\SimpleContainer;

class DynamicReferenceTest extends TestCase
{
    public function createContainer(): ContainerInterface
    {
        $container = new SimpleContainer(
            [
                EngineInterface::class => new EngineMarkOne(),
            ],
            static function (string $id) use (&$container) {
                if ($id === ContainerInterface::class) {
                    return $container;
                }
                if ($id === Injector::class) {
                    return new Injector($container);
                }
                throw new NotFoundException($id);
            }
        );
        return $container;
    }

    public function testString(): void
    {
        $ref = DynamicReference::to(EngineInterface::class);
        $this->assertInstanceOf(EngineMarkOne::class, $ref->resolve($this->createContainer()));
    }

    public function testClosure(): void
    {
        $ref = DynamicReference::to(
            fn (ContainerInterface $container) => $container->get(EngineInterface::class)
        );
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
