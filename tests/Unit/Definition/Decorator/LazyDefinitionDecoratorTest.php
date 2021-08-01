<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Definition\Decorator\LazyDefinitionDecorator;
use Yiisoft\Factory\Tests\Support\NotFinalClass;
use Yiisoft\Factory\Tests\Support\Phone;
use Yiisoft\Factory\Tests\TestHelper;

final class LazyDefinitionDecoratorTest extends TestCase
{
    public function testDecorateFinalClass(): void
    {
        $dependencyResolver = TestHelper::createDependencyResolver();
        $factory= new LazyLoadingValueHolderFactory();

        $class = Phone::class;

        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
        $definition = new LazyDefinitionDecorator($factory, $definition, $class);

        $this->expectException(InvalidProxiedClassException::class);
        $definition->resolve($dependencyResolver);
    }

    public function testDecorateNotFinalClass(): void
    {
        $dependencyResolver = TestHelper::createDependencyResolver();
        $factory= new LazyLoadingValueHolderFactory();

        $class = NotFinalClass::class;

        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
        $definition = new LazyDefinitionDecorator($factory, $definition, $class);

        $phone = $definition->resolve($dependencyResolver);

        self::assertInstanceOf(LazyLoadingInterface::class, $phone);
    }
}
