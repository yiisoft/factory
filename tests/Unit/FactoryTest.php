<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\EngineMarkTwo;
use Yiisoft\Factory\Tests\Support\Immutable;
use Yiisoft\Factory\Tests\Support\TwoParametersDependency;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class FactoryTest extends TestCase
{
    public function testCanCreateByAlias(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, ['engine' => EngineMarkOne::class]);

        $one = $factory->create('engine');
        $two = $factory->create('engine');

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testCanCreateByInterfaceAsStringDefinition(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [EngineInterface::class => EngineMarkOne::class]);

        $one = $factory->create(EngineInterface::class);
        $two = $factory->create(EngineInterface::class);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testCanCreateByInterfaceAsReferenceDefinition(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [EngineInterface::class => EngineMarkOne::class]);

        $one = $factory->create(Reference::to(EngineInterface::class));
        $two = $factory->create(Reference::to(EngineInterface::class));

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * Factory should always return new instance even if an object is set to it.
     * In this case it is being cloned.
     */
    public function testObjectIsCloned(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, ['engine' => new EngineMarkOne()]);

        $one = $factory->create('engine');
        $two = $factory->create('engine');

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * If class name is used as ID, factory must try create given class.
     */
    public function testCreateClassNotDefinedInConfig(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $one = $factory->create(EngineMarkOne::class);
        $two = $factory->create(EngineMarkOne::class);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * Configuration specified in {@see Factory::create()} should be merged with configuration stored in a factory.
     */
    public function testMergeFactoryConfig(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [
            EngineMarkOne::class => [
                'class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create([
            'class' => EngineMarkOne::class,
        ]);

        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertEquals(42, $instance->getNumber());
    }

    /**
     * Configuration specified in {@see Factory::create()} has more priority than configuration stored in a factory.
     */
    public function testOverrideFactoryConfig(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [
            EngineMarkOne::class => [
                'class' => EngineMarkOne::class,
                'setNumber()' => [42],


            ],
        ]);

        $instance = $factory->create([
            'class' => EngineMarkOne::class,
            'setNumber()' => [43],

        ]);

        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertEquals(43, $instance->getNumber());
    }

    public function testGetByAlias(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [
            'engine' => EngineMarkOne::class,
        ]);

        $one = $factory->get('engine');
        $two = $factory->get('engine');

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testTrivialDefinition(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);
        $factory->set(EngineMarkOne::class, EngineMarkOne::class);

        $one = $factory->get(EngineMarkOne::class);
        $two = $factory->get(EngineMarkOne::class);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * TODO: is it possible to remove second argument of {@see Factory::create()} and always pass definition instead?
     */
    public function testCreateWithParams(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $one = $factory->create(Car::class, [$factory->get(EngineMarkOne::class)]);
        $two = $factory->create(Car::class, [$factory->get(EngineMarkTwo::class)]);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one->getEngine());
        $this->assertInstanceOf(EngineMarkTwo::class, $two->getEngine());
    }

    public function testCreateWithNamedParams(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $one = $factory->create(Car::class, ['engine' => $factory->get(EngineMarkOne::class)]);
        $two = $factory->create(Car::class, ['engine' => $factory->get(EngineMarkTwo::class)]);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one->getEngine());
        $this->assertInstanceOf(EngineMarkTwo::class, $two->getEngine());
    }

    public function testCreateWithCallableValuesInParams(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $object = $factory->create(TwoParametersDependency::class, [
            'firstParameter' => 'date',
            'secondParameter' => 'time',
        ]);

        $this->assertInstanceOf(TwoParametersDependency::class, $object);
        $this->assertSame('date', $object->getFirstParameter());
        $this->assertSame('time', $object->getSecondParameter());
    }

    public function testCreateWithInvalidParams(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $this->expectException(InvalidConfigException::class);

        $factory->create(TwoParametersDependency::class, ['firstParam' => 'param1', 1 => 'param2']);
    }

    public function testCreateWithRandomOrderedParams(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $object = $factory->create(TwoParametersDependency::class, [1 => 'param2', 0 => 'param1']);

        $this->assertInstanceOf(TwoParametersDependency::class, $object);
        $this->assertSame('param1', $object->getFirstParameter());
        $this->assertSame('param2', $object->getSecondParameter());
    }

    /**
     * In case class to be created has dependencies, these are looked for in DI container.
     */
    public function testResolveDependenciesUsingContainer(): void
    {
        $origin = new EngineMarkOne();
        $container = new SimpleContainer([EngineInterface::class => $origin]);
        $factory = new Factory($container);

        $one = $factory->create(Car::class);
        $two = $factory->create(Car::class);

        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $two->getEngine());
        $this->assertNotSame($one, $two);
        $this->assertNotSame($origin, $one);
        $this->assertNotSame($origin, $two);
        $this->assertSame($one->getEngine(), $two->getEngine());
    }

    /**
     * Falling back to container is not desired because it would likely result to implicitly returning the
     * same instance of the object when calling {@see Factory::create()} multiple times with the same ID.
     */
    public function testDoNotFallbackToContainer(): void
    {
        $engine = new EngineMarkOne();
        $engine->setNumber(42);
        $container = new SimpleContainer([EngineMarkOne::class => $engine]);
        $factory = new Factory($container);

        $instance = $factory->create(EngineMarkOne::class);

        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertNotEquals(42, $instance->getNumber());
        $this->assertNotSame($engine, $instance);
    }

    /**
     * When resolving dependencies, factory should rely on container only
     */
    public function testDoNotResolveDependenciesFromFactory(): void
    {
        $container = new SimpleContainer([EngineInterface::class => new EngineMarkOne()]);
        $factory = new Factory($container, [
            EngineInterface::class => [
                'class' => EngineMarkOne::class,
                'setNumber' => [42],
            ],
        ]);

        $instance = $factory->create(Car::class);

        $this->assertInstanceOf(Car::class, $instance);
        $this->assertInstanceOf(EngineMarkOne::class, $instance->getEngine());
        $this->assertEquals(0, $instance->getEngine()->getNumber());
    }

    public function testCreateFactory(): void
    {
        $container = new SimpleContainer([ContainerInterface::class => &$container]);
        $factory = new Factory($container, [
            'factoryObject' => [
                'class' => Factory::class,
                '__construct()' => [
                    'container' => Reference::to(ContainerInterface::class),
                    'definitions' => [],
                ],
            ],
        ]);

        $oneFactoryObject = $factory->create('factoryObject');
        $otherFactoryObject = $factory->create('factoryObject');

        $this->assertNotSame($oneFactoryObject, $otherFactoryObject);
        $this->assertNotSame($oneFactoryObject, $factory);
        $this->assertInstanceOf(Factory::class, $oneFactoryObject);
        $this->assertInstanceOf(Factory::class, $otherFactoryObject);
    }

    public function testCreateFactoryImmutable(): void
    {
        $factory = new Factory(new SimpleContainer(), [
            'immutableObject' => [
                'class' => Immutable::class,
                'id()' => ['id-testMe'],
                'fieldImmutable()' => ['testMe'],
            ],
        ]);

        $oneImmutableObject = $factory->create('immutableObject');
        $otherImmutableObject = (new Immutable())->fieldImmutable('testMe');
        $otherImmutableObject->id('id-testMe');

        $this->assertEquals($oneImmutableObject, $otherImmutableObject);
    }
}
