<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use DateTime;
use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ClassDefinition;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Exception\NotInstantiableException;
use Yiisoft\Factory\Definition\DefinitionExtractor;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Factory\Tests\Support\NullableConcreteDependency;
use Yiisoft\Factory\Tests\Support\NullableInterfaceDependency;
use Yiisoft\Factory\Tests\Support\OptionalConcreteDependency;
use Yiisoft\Factory\Tests\Support\OptionalInterfaceDependency;
use Yiisoft\Factory\Tests\TestHelper;

class DefinitionExtractorTest extends TestCase
{
    public function testResolveConstructor(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createFactoryContainer();

        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(DateTime::class);


        $this->assertCount(2, $dependencies);


        // Since reflection for built in classes does not get default values.
        if (PHP_VERSION_ID >= 80000) {
            $this->assertEquals('now', $dependencies['datetime']->resolve($container));
        } else {
            $this->assertEquals(null, $dependencies['time']->resolve($container));
        }

        $this->assertEquals(null, $dependencies['timezone']->resolve($container));
    }

    public function testResolveCarConstructor(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createFactoryContainer();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(Car::class);

        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(ClassDefinition::class, $dependencies['engine']);
        $this->expectException(NotInstantiableException::class);
        $dependencies['engine']->resolve($container);
    }

    public function testResolveGearBoxConstructor(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createFactoryContainer();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(GearBox::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(5, $dependencies['maxGear']->resolve($container));
    }

    public function testOptionalInterfaceDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createFactoryContainer();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(OptionalInterfaceDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['engine']->resolve($container));
    }

    public function testNullableInterfaceDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createFactoryContainer();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(NullableInterfaceDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['engine']->resolve($container));
    }

    public function testOptionalConcreteDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createFactoryContainer();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(OptionalConcreteDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['car']->resolve($container));
    }

    public function testNullableConcreteDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createFactoryContainer();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(NullableConcreteDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['car']->resolve($container));
    }
}
