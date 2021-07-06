<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\CallableDefinition;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\CarFactory;
use Yiisoft\Factory\Tests\Support\ColorInterface;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class CallableDefinitionTest extends TestCase
{
    public function testDynamicCallable(): void
    {
        $definition = new CallableDefinition([CarFactory::class, 'createWithColor']);

        $container = new SimpleContainer(
            [
                CarFactory::class => new CarFactory(),
                ColorInterface::class => new ColorPink(),
            ]
        );

        /** @var Car $car */
        $car = $definition->resolve($container);

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(ColorPink::class, $car->getColor());
    }
}
