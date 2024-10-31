<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Factory\NotFoundException;
use Yiisoft\Factory\StrictFactory;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class StrictFactoryTest extends TestCase
{
    public function testBase(): void
    {
        $factory = new StrictFactory(
            new SimpleContainer(),
            [
                'engine' => EngineMarkOne::class,
            ]
        );

        $this->assertInstanceOf(EngineMarkOne::class, $factory->create('engine'));

        $this->expectException(NotFoundException::class);
        $factory->create(EngineMarkOne::class);
    }

    public function testCreateWithInvalidFactoryDefinitionWithValidation(): void
    {
        $this->expectException(InvalidConfigException::class);
        new StrictFactory(new SimpleContainer(), ['x' => 42], true);
    }

    public function testCreateWithInvalidFactoryDefinitionWithoutValidation(): void
    {
        $factory = new StrictFactory(new SimpleContainer(), ['x' => 42], false);

        $this->expectException(InvalidConfigException::class);
        $factory->create('x');
    }
}
