<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Yiisoft\Factory\Definition\ParameterDefinition;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\EngineMarkTwo;

final class ParameterDefinitionTest extends TestCase
{
    public function testResolveObjectWithFactory(): void
    {
        $engine = new EngineMarkTwo();

        $reflection = new ReflectionClass(Car::class);
        $parameter = $reflection->getConstructor()->getParameters()[0];

        $definition = new ParameterDefinition($parameter);
        $definition->setValue($engine);

        $value = $definition->resolve(new Factory());

        $this->assertInstanceOf(EngineMarkTwo::class, $value);
        $this->assertNotSame($value, $engine);
    }
}
