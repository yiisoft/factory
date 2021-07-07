<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ValueDefinition;

final class ValueDefinitionTest extends TestCase
{
    public function testGetType(): void
    {
        $definition = new ValueDefinition(42, 'integer');

        $this->assertSame('integer', $definition->getType());
    }
}
