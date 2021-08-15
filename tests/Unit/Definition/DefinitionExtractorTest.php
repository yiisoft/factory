<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use DateTime;
use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ClassDefinition;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Definition\ParameterDefinition;
use Yiisoft\Factory\Exception\NotInstantiableClassException;
use Yiisoft\Factory\Definition\DefinitionExtractor;
use Yiisoft\Factory\Exception\NotInstantiableException;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Factory\Tests\Support\NullableConcreteDependency;
use Yiisoft\Factory\Tests\Support\NullableInterfaceDependency;
use Yiisoft\Factory\Tests\Support\OptionalConcreteDependency;
use Yiisoft\Factory\Tests\Support\OptionalInterfaceDependency;
use Yiisoft\Factory\Tests\TestHelper;

final class DefinitionExtractorTest extends TestCase
{
    public function testResolveConstructor(): void
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('Can not determine default value of PHP internal parameters in PHP < 8.0.');
        }

        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();

        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(DateTime::class);

        // Since reflection for built in classes does not get default values.
        $this->expectException(NotInstantiableException::class);
        $this->expectExceptionMessage(
            'Can not determine default value of parameter "time" when instantiating' .
            ' "DateTime::__construct()" because it is PHP internal. Please specify argument explicitly.'
        );
        $dependencies['time']->resolve($container);
    }

    public function testResolveCarConstructor(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(Car::class);

        $this->assertCount(2, $dependencies);
        $this->assertInstanceOf(ClassDefinition::class, $dependencies['engine']);
        $this->assertInstanceOf(ParameterDefinition::class, $dependencies['moreEngines']);

        $this->expectException(NotInstantiableClassException::class);
        $dependencies['engine']->resolve($container);
    }

    public function testResolveGearBoxConstructor(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(GearBox::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(5, $dependencies['maxGear']->resolve($container));
    }

    public function testOptionalInterfaceDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(OptionalInterfaceDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['engine']->resolve($container));
    }

    public function testNullableInterfaceDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(NullableInterfaceDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['engine']->resolve($container));
    }

    public function testOptionalConcreteDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(OptionalConcreteDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['car']->resolve($container));
    }

    public function testNullableConcreteDependency(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(NullableConcreteDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies['car']->resolve($container));
    }
}
