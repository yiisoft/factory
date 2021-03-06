<?php

declare(strict_types=1);

namespace YYiisoft\Factory\Tests\Php8;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definitions\ClassDefinition;
use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Extractors\DefinitionExtractor;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\UnionCar;

class DefinitionExtractorUnionTypesTest extends TestCase
{
    public function testResolveCarConstructor(): void
    {
        $resolver = new DefinitionExtractor();
        $container = new Factory();
        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(UnionCar::class);

        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(ClassDefinition::class, $dependencies['engine']);
        $resolved = $dependencies['engine']->resolve($container);
        $this->assertInstanceOf(EngineMarkOne::class, $resolved);
    }
}
