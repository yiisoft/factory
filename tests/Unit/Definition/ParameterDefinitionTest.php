<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Yiisoft\Factory\Definition\ParameterDefinition;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\EngineMarkTwo;
use Yiisoft\Factory\Tests\TestHelper;

final class ParameterDefinitionTest extends TestCase
{
    public function testResolveObjectWithFactory(): void
    {
        $factoryContainer = TestHelper::createFactoryContainer();

        $engine = new EngineMarkTwo();

        $reflection = new ReflectionClass(Car::class);
        $parameter = $reflection->getConstructor()->getParameters()[0];

        $definition = new ParameterDefinition($parameter);
        $definition->setValue($engine);

        $value = $definition->resolve($factoryContainer);

        $this->assertInstanceOf(EngineMarkTwo::class, $value);
        $this->assertNotSame($value, $engine);
    }
}
