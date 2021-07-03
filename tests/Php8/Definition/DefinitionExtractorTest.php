<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Php8\Definition;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ClassDefinition;
use Yiisoft\Factory\Definition\DefinitionExtractor;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\UnionCar;

class DefinitionExtractorTest extends TestCase
{
    public function testResolveCarConstructor(): void
    {
        $extractor = DefinitionExtractor::getInstance();
        $container = new Factory();

        $dependencies = $extractor->fromClassName(UnionCar::class);

        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(ClassDefinition::class, $dependencies['engine']);
        $resolved = $dependencies['engine']->resolve($container);
        $this->assertInstanceOf(EngineMarkOne::class, $resolved);
    }
}
