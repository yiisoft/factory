<?php

namespace YYiisoft\Factory\Tests\Unit\Resolvers;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definitions\ClassDefinition;
use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Definitions\InvalidDefinition;
use Yiisoft\Factory\Exceptions\NotInstantiableException;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Resolvers\ClassNameResolver;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Factory\Tests\Support\HasNoDefaultValue\ArrayArgument;
use Yiisoft\Factory\Tests\Support\HasNoDefaultValue\BooleanArgument;
use Yiisoft\Factory\Tests\Support\HasNoDefaultValue\CallableArgument;
use Yiisoft\Factory\Tests\Support\HasNoDefaultValue\IntArgument;
use Yiisoft\Factory\Tests\Support\HasNoDefaultValue\ObjectArgument;
use Yiisoft\Factory\Tests\Support\HasNoDefaultValue\StringArgument;
use Yiisoft\Factory\Tests\Support\NullableConcreteDependency;
use Yiisoft\Factory\Tests\Support\NullableInterfaceDependency;
use Yiisoft\Factory\Tests\Support\OptionalConcreteDependency;
use Yiisoft\Factory\Tests\Support\OptionalInterfaceDependency;

class ClassNameResolverTest extends TestCase
{
    /**
     * @dataProvider constructorsProvider()
     * @param string $class
     * @param array $arguments
     * @throws \Yiisoft\Factory\Exceptions\NotInstantiableException
     */
    public function testResolveConstructors(string $class, array $arguments): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();
        $dependencies = $resolver->resolveConstructor($class);

        $this->assertCount(count($arguments), $dependencies);
        foreach ($arguments as $index => $value) {
            $this->assertSame($value, $dependencies[$index]->resolve($container));
        }
    }

    /**
     * @dataProvider callablesProvider()
     * @param callable $callback
     * @param array $arguments
     */
    public function testResolveCallables(callable $callback, array $arguments): void
    {
        $resolver = new ClassNameResolver();
        $container = new Factory();

        $dependencies = $resolver->resolveCallable($callback);

        $this->assertCount(count($arguments), $dependencies);
        foreach ($arguments as $index => $value) {
            $this->assertSame($value, $dependencies[$index]->resolve($container));
        }
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

    /**
     * @dataProvider hasNoDefaultValueProvider()
     * @param string $class
     * @throws \Yiisoft\Factory\Exceptions\NotInstantiableException
     */
    public function testFailResolveArgument(string $class): void
    {
        $resolver = new ClassNameResolver();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->resolveConstructor($class);

        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(InvalidDefinition::class, $dependencies[0]);
    }

    public function constructorsProvider(): array
    {
        return [
            [\DateTime::class, [null, null]],
            [NullableConcreteDependency::class, [null]],
            [OptionalConcreteDependency::class, [null]],
            [NullableInterfaceDependency::class, [null]],
            [OptionalInterfaceDependency::class, [null]],
            [GearBox::class, [5]],
        ];
    }

    public function callablesProvider(): array
    {
        return [
            [
                function (string $string = '') {
                },
                [''],
            ],
            [
                function (?string $nullableWithDefaultValue = '') {
                },
                [''],
            ],
            [
                function (?string $nullable) {
                },
                [null],
            ],
            [
                function (?int $nullable, int $b = 0) {
                },
                [null, 0],
            ],
        ];
    }

    public function hasNoDefaultValueProvider(): array
    {
        return [
            'array' => [ArrayArgument::class],
            'callable' => [CallableArgument::class],
            'int' => [IntArgument::class],
            'object' => [ObjectArgument::class],
            'string' => [StringArgument::class],
            'bool' => [BooleanArgument::class],
        ];
    }
}
