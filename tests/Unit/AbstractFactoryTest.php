<?php

namespace Yiisoft\Factory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use League\Container\Container;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\EngineMarkTwo;

/**
 * General tests for factory.
 * To be extended for specific containers.
 */
abstract class AbstractFactoryTest extends TestCase
{
    abstract public function createContainer(array $definitions = []): ContainerInterface;

    public function testCanCreateByAlias(): void
    {
        $factory = new Factory($this->createContainer(), [
            'engine' => EngineMarkOne::class,
        ]);
        $one = $factory->create('engine');
        $two = $factory->create('engine');
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
        $factory = new Factory($this->createContainer(), [
            'engine' => new EngineMarkOne(),
        ]);
        $one = $factory->create('engine');
        $two = $factory->create('engine');
        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * If class name is used as ID, factory should try creating such class.
     */
    public function testCreateAClassIfNotDefinedInConfig(): void
    {
        $factory = new Factory($this->createContainer());
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
        $factory = new Factory($this->createContainer(), [
            EngineMarkOne::class => [
                '__class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create([
            '__class' => EngineMarkOne::class,
        ]);
        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertEquals(42, $instance->getNumber());
    }

    /**
     * Configuration specified in {@see Factory::create()} has more priority than configuration stored in a factory.
     */
    public function testOverrideFactoryConfig(): void
    {
        $factory = new Factory($this->createContainer(), [
            EngineMarkOne::class => [
                '__class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create([
            '__class' => EngineMarkOne::class,
            'setNumber()' => [43]
        ]);
        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertEquals(43, $instance->getNumber());
    }

    public function testGetByAlias(): void
    {
        $factory = new Factory($this->createContainer(), [
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
        $factory = new Factory($this->createContainer());
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
        $factory = new Factory($this->createContainer());
        $one = $factory->create(Car::class, [$factory->get(EngineMarkOne::class)]);
        $two = $factory->create(Car::class, [$factory->get(EngineMarkTwo::class)]);
        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one->getEngine());
        $this->assertInstanceOf(EngineMarkTwo::class, $two->getEngine());
    }

    /**
     * In case class to be created has dependencies, these are looked for in DI container.
     */
    public function testResolveDependenciesUsingContainer(): void
    {
        $container = $this->createContainer([
            EngineInterface::class => new EngineMarkOne(),
        ]);
        $factory = new Factory($container);
        $one = $factory->create(Car::class);
        $two = $factory->create(Car::class);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $two->getEngine());
        $this->assertNotSame($one, $two);
        $this->assertSame($one->getEngine(), $two->getEngine());
    }

    /**
     * Falling back to container is not desired because it would likely result to implicitly returning the
     * same instance of the object when calling {@see Factory::create()} multiple times with the same ID.
     */
    public function testDoNotFallbackToContainer(): void
    {
        $container = $this->createContainer([
            EngineMarkOne::class => [
                '__class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $factory = new Factory($container);

        $instance = $factory->create(EngineMarkOne::class);
        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertNotEquals(42, $instance->getNumber());
    }

    /**
     * When resolving dependencies, factory should rely on container only
     */
    public function testDoNotResolveDependenciesFromFactory(): void
    {
        $container = $this->createContainer([
            EngineInterface::class => new EngineMarkOne(),
        ]);
        $factory = new Factory($container, [
            EngineInterface::class => [
                '__class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create(Car::class);
        $this->assertInstanceOf(Car::class, $instance);
        $this->assertInstanceOf(EngineMarkOne::class, $instance->getEngine());

        $this->assertEquals(0, $instance->getEngine()->getNumber());
    }
}
