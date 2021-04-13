<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use League\Container\Container;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Definition\Reference;
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
    abstract public function createContainer(iterable $definitions = []): ContainerInterface;

    abstract public function setupContainer(ContainerInterface $container, iterable $definitions = []): ContainerInterface;

    public function testCreateByAlias(): void
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

    public function testSingleton(): void
    {
        $factory = new Factory($this->createContainer(), [
            'engine' => new EngineMarkOne(),
        ]);
        $one = $factory->create('engine');
        $two = $factory->create('engine');
        $this->assertSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testCreateByClass(): void
    {
        $factory = new Factory($this->createContainer());
        $one = $factory->create(EngineMarkOne::class);
        $two = $factory->create(EngineMarkOne::class);
        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
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

    public function testGetByClass(): void
    {
        $factory = new Factory($this->createContainer());
        $one = $factory->get(EngineMarkOne::class);
        $two = $factory->get(EngineMarkOne::class);
        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testFactoryInContainer(): void
    {
        $container = $this->createContainer();
        $this->setupContainer($container, [
            ContainerInterface::class => $container,
        ]);
        $factory = new Factory($container, [
            'factory' => [
                'class' => Factory::class,
                '__construct()' => [
                    'parent' => Reference::to(ContainerInterface::class),
                    'definitions' => [],
                ],
            ],
        ]);
        $one = $factory->create('factory');
        $two = $factory->create('factory');
        $this->assertNotSame($one, $two);
        $this->assertNotSame($one, $factory);
        $this->assertInstanceOf(Factory::class, $one);
        $this->assertInstanceOf(Factory::class, $two);
    }

    public function testCreateWithParams(): void
    {
        $factory = new Factory(new Container());
        $one = $factory->create(Car::class, [$factory->get(EngineMarkOne::class)]);
        $two = $factory->create(Car::class, [$factory->get(EngineMarkTwo::class)]);
        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one->getEngine());
        $this->assertInstanceOf(EngineMarkTwo::class, $two->getEngine());
    }

    public function testCreateWithDependencyInContainer(): void
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

}
