<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\Reference;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Tests\Support\EngineInterface;

final class ReferenceTest extends TestCase
{
    public function testInvalid(): void
    {
        $this->expectException(InvalidConfigException::class);
        Reference::to(['class' => EngineInterface::class]);
    }
}
