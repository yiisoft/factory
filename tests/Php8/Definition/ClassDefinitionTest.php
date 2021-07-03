<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Php8\Definition;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\Factory\Definition\ClassDefinition;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ClassDefinitionTest extends TestCase
{
    public function testResolveRequiredUnionTypeWithIncorrectTypeInContainer(): void
    {
        $class = stdClass::class . '|' . GearBox::class;

        $definition = new ClassDefinition($class, false);

        $container = new SimpleContainer(
            [
                stdClass::class => 42,
                GearBox::class => 7,
            ]
        );

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Container returned incorrect type "integer" for service "' . $class . '".'
        );
        $definition->resolve($container);
    }

    public function testResolveOptionalUnionTypeWithIncorrectTypeInContainer(): void
    {
        $class = stdClass::class . '|' . GearBox::class;

        $definition = new ClassDefinition($class, true);

        $container = new SimpleContainer(
            [
                stdClass::class => 42,
                GearBox::class => 7,
            ]
        );

        $result = $definition->resolve($container);

        $this->assertNull($result);
    }
}