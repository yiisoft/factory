<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\Factory\Definition\ValueDefinition;
use Yiisoft\Factory\Factory;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ValueDefinitionTest extends TestCase
{
    public function testGetType(): void
    {
        $definition = new ValueDefinition(42, 'integer');

        $this->assertSame('integer', $definition->getType());
    }

    public function testDoNotCloneObjectFromContainer(): void
    {
        $object = new stdClass();

        $definition = new ValueDefinition($object, 'object');
        $value = $definition->resolve(new SimpleContainer());

        $this->assertSame($object, $value);
    }

    public function testCloneObjectFromFactory(): void
    {
        $object = new stdClass();

        $definition = new ValueDefinition($object, 'object');
        $value = $definition->resolve(new Factory());

        $this->assertNotSame($object, $value);
    }
}
