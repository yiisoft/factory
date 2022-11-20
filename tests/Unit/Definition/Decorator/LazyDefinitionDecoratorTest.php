<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Factory\Definition\Decorator\LazyDefinitionDecorator;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\NotFinalClass;
use Yiisoft\Factory\Tests\Support\Phone;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class LazyDefinitionDecoratorTest extends TestCase
{
    public function testDecorateFinalClass(): void
    {
        $container = new SimpleContainer();
        $factory = new LazyLoadingValueHolderFactory();

        $class = Phone::class;

        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
        $definition = new LazyDefinitionDecorator($factory, $definition, $class);

        $this->expectException(InvalidProxiedClassException::class);
        $definition->resolve($container);
    }

    public function testDecorateNotFinalClass(): void
    {
        $container = new SimpleContainer();
        $factory = new LazyLoadingValueHolderFactory();

        $class = NotFinalClass::class;

        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
        $definition = new LazyDefinitionDecorator($factory, $definition, $class);

        $phone = $definition->resolve($container);

        self::assertInstanceOf(LazyLoadingInterface::class, $phone);
    }

    public function testDecorateInterface(): void
    {
        $container = new SimpleContainer();
        $factory = new LazyLoadingValueHolderFactory();

        $class = EngineInterface::class;

        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
        $definition = new LazyDefinitionDecorator($factory, $definition, $class);

        $phone = $definition->resolve($container);

        self::assertInstanceOf(LazyLoadingInterface::class, $phone);
    }
}
