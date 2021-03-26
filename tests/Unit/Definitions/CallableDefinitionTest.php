<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definitions;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definitions\CallableDefinition;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\CarFactory;
use Yiisoft\Factory\Tests\Support\ColorInterface;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class CallableDefinitionTest extends TestCase
{
    public function testDynamicCallable(): void
    {
        $definition = new CallableDefinition([CarFactory::class, 'createWithColor']);

        $injector = new Injector(new SimpleContainer([
            ColorInterface::class => new ColorPink(),
        ]));
        $container = new SimpleContainer([
            CarFactory::class => new CarFactory(),
            Injector::class => $injector,
        ]);

        /** @var Car $car */
        $car = $definition->resolve($container);

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(ColorPink::class, $car->getColor());
    }
}
