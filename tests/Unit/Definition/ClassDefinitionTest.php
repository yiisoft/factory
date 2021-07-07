<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\Factory\Definition\ClassDefinition;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ClassDefinitionTest extends TestCase
{
    public function testGetType(): void
    {
        $definition = new ClassDefinition(stdClass::class, true);

        $this->assertSame(stdClass::class, $definition->getType());
    }

    public function testResolveWithIncorrectTypeInContainer(): void
    {
        $definition = new ClassDefinition(stdClass::class, true);

        $container = new SimpleContainer([stdClass::class => 42]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Container returned incorrect type "integer" for service "' . stdClass::class . '".'
        );
        $definition->resolve($container);
    }
}
