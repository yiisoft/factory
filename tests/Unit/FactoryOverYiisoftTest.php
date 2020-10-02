<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use Psr\Container\ContainerInterface;
use Yiisoft\Di\Container;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\Factory\Tests\Support\Immutable;

/**
 * Test the Factory over Yiisoft Container.
 */
class FactoryOverYiisoftTest extends AbstractFactoryTest
{
    public function createContainer(iterable $definitions = []): ContainerInterface
    {
        return new Container($definitions);
    }

    public function testCreateFactory(): void
    {
        $container = $this->createContainer();
        $factory = new Factory($container, [
            'factoryObject' => [
                '__class' => Factory::class,
                '__construct()' => [
                    'container'     => Reference::to(ContainerInterface::class),
                    'definitions'   => [],
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
        $container = $this->createContainer();
        $factory = new Factory($container, [
            'immutableObject' => [
                '__class' => Immutable::class,
                'id()' => ['id-testMe'],
                'fieldImmutable()' => ['testMe'],
            ]
        ]);
        $oneImmutableObject = $factory->create('immutableObject');
        $otherImmutableObject = (new Immutable())->fieldImmutable('testMe');
        $otherImmutableObject->id('id-testMe');
        $this->assertEquals($oneImmutableObject, $otherImmutableObject);
    }
}
