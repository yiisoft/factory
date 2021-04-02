<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\Factory\Exceptions\NotFoundException;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\Immutable;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class FactoryTest extends TestCase
{
    public function testCreateFactory(): void
    {
        $container = new SimpleContainer(
            [],
            static function (string $id) use (&$container) {
                if ($id === ContainerInterface::class) {
                    return $container;
                }
                throw new NotFoundException($id);
            }
        );

        $factory = new Factory($container, [
            'factoryObject' => [
                '__class' => Factory::class,
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
                '__class' => Immutable::class,
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
