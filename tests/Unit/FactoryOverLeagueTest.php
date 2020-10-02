<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use Psr\Container\ContainerInterface;
use League\Container\Container;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Definitions\Reference;

/**
 * Test the Factory over League Container.
 */
class FactoryOverLeagueTest extends AbstractFactoryTest
{
    public function createContainer(iterable $definitions = []): ContainerInterface
    {
        return $this->setupContainer(new Container(), $definitions);
    }

    public function setupContainer(ContainerInterface $container, iterable $definitions = []): ContainerInterface
    {
        foreach ($definitions as $id => $definition) {
            $container->add($id, $definition);
        }

        return $container;
    }

    public function testCreateFactory(): void
    {
        $container = $this->createContainer();
        $this->setupContainer($container, [ContainerInterface::class => $container]);
        $factory = new Factory($container, [
            'factory' => [
                '__class' => Factory::class,
                '__construct()' => [
                    'container'     => Reference::to(ContainerInterface::class),
                    'definitions'   => [],
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
}
