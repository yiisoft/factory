<?php

namespace YYiisoft\Factory\Tests\Unit\Resolvers;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Definitions\ClassDefinition;
use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Exceptions\NotInstantiableException;
use Yiisoft\Factory\Resolvers\ClassNameResolver;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Factory\Tests\Support\NullableConcreteDependency;
use Yiisoft\Factory\Tests\Support\NullableInterfaceDependency;
use Yiisoft\Factory\Tests\Support\OptionalConcreteDependency;
use Yiisoft\Factory\Tests\Support\OptionalInterfaceDependency;

class ClassNameResolverTest extends TestCase
{
    public function testResolveConstructor(): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor(\DateTime::class);

        $this->assertCount(2, $dependencies);
        // Since reflection for built in classes does not get default values.
        $this->assertEquals(null, $dependencies[0]->resolve($container));
        $this->assertEquals(null, $dependencies[1]->resolve($container));
    }

    public function testResolveCarConstructor(): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor(Car::class);

        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(ClassDefinition::class, $dependencies[0]);
        $this->expectException(NotInstantiableException::class);
        $dependencies[0]->resolve($container);
    }

    public function testResolveGearBoxConstructor(): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor(GearBox::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(5, $dependencies[0]->resolve($container));
    }

    public function testOptionalInterfaceDependency(): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor(OptionalInterfaceDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies[0]->resolve($container));
    }
    public function testNullableInterfaceDependency(): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor(NullableInterfaceDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies[0]->resolve($container));
    }

    public function testOptionalConcreteDependency(): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor(OptionalConcreteDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies[0]->resolve($container));
    }
    public function testNullableConcreteDependency(): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor(NullableConcreteDependency::class);
        $this->assertCount(1, $dependencies);
        $this->assertEquals(null, $dependencies[0]->resolve($container));
    }
}
