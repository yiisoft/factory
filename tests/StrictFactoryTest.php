<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Factory\NotFoundException;
use Yiisoft\Factory\StrictFactory;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class StrictFactoryTest extends TestCase
{
    public function testBase(): void
    {
        $factory = new StrictFactory([
            'engine' => EngineMarkOne::class,
        ]);

        $this->assertInstanceOf(EngineMarkOne::class, $factory->create('engine'));

        $this->expectException(NotFoundException::class);
        $factory->create(EngineMarkOne::class);
    }

    public function testWithContainer(): void
    {
        $engine = new EngineMarkOne();
        $factory = new StrictFactory(
            [
                'car' => Car::class,
            ],
            new SimpleContainer([
                EngineInterface::class => $engine,
            ]),
        );

        $object = $factory->create('car');

        $this->assertInstanceOf(Car::class, $object);
        $this->assertSame($engine, $object->getEngine());
    }

    public function testCreateWithInvalidFactoryDefinitionWithValidation(): void
    {
        $this->expectException(InvalidConfigException::class);
        new StrictFactory(['x' => 42]);
    }

    public function testCreateWithInvalidFactoryDefinitionWithoutValidation(): void
    {
        $factory = new StrictFactory(['x' => 42], validate: false);

        $this->expectException(InvalidConfigException::class);
        $factory->create('x');
    }
}
