<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Php8\Definition;

use DateTime;
use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ClassDefinition;
use Yiisoft\Factory\Definition\DefinitionExtractor;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\UnionCar;
use Yiisoft\Factory\Tests\TestHelper;

final class DefinitionExtractorTest extends TestCase
{
    public function testResolveConstructor(): void
    {
        $resolver = DefinitionExtractor::getInstance();
        $container = TestHelper::createDependencyResolver();

        /** @var DefinitionInterface[] $dependencies */
        $dependencies = $resolver->fromClassName(DateTime::class);

        $this->assertCount(2, $dependencies);
        $this->assertEquals('now', $dependencies['datetime']->resolve($container));
        $this->assertEquals(null, $dependencies['timezone']->resolve($container));
    }

    public function testResolveCarConstructor(): void
    {
        $extractor = DefinitionExtractor::getInstance();
        $dependencyResolver = TestHelper::createDependencyResolver();

        $dependencies = $extractor->fromClassName(UnionCar::class);

        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(ClassDefinition::class, $dependencies['engine']);
        $resolved = $dependencies['engine']->resolve($dependencyResolver);
        $this->assertInstanceOf(EngineMarkOne::class, $resolved);
    }
}
