<?php

namespace Yiisoft\Factory\Tests\Unit;

use Psr\Container\ContainerInterface;
use Yiisoft\Di\Container;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Definitions\Reference;

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
        $container = $this->createContainer([
            ContainerInterface::class => static function (ContainerInterface $container) {
                return $container;
            },
        ]);
        $factory = new Factory($container, [
            'factory' => [
                '__class' => Factory::class,
                '__construct()' => [
                    'parent'        => Reference::to(ContainerInterface::class),
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
