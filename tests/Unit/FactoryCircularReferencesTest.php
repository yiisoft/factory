<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Reference;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\NotFoundException;
use Yiisoft\Factory\Tests\Support\Circular\Chicken;
use Yiisoft\Factory\Tests\Support\Circular\CircularA;
use Yiisoft\Factory\Tests\Support\Circular\CircularB;
use Yiisoft\Factory\Tests\Support\Circular\Egg;
use Yiisoft\Factory\Tests\Support\Circular\TreeItem;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class FactoryCircularReferencesTest extends TestCase
{
    public function testOptionalCircularClassDependency(): void
    {
        $factory = new Factory(new SimpleContainer(), [
            CircularA::class => CircularA::class,
            CircularB::class => CircularB::class,
        ]);

        $a = $factory->create(CircularA::class);

        $this->assertInstanceOf(CircularB::class, $a->b);
        $this->assertNull($a->b->a);
    }

    public function testCircularClassDependency(): void
    {
        $factory = new Factory(
            new SimpleContainer(),
            [
                Chicken::class => Chicken::class,
                Egg::class => Egg::class,
            ]
        );

        $this->expectException(CircularReferenceException::class);
        $factory->create(Chicken::class);
    }

    public function testCircularClassDependencyWithoutDefinition(): void
    {
        $factory = new Factory(new SimpleContainer(), [
            Egg::class => Egg::class,
            Chicken::class => Chicken::class,
        ]);

        $this->expectException(CircularReferenceException::class);
        $factory->create(Chicken::class);
    }

    public function testCircularReferences(): void
    {
        $factory = new Factory(
            new SimpleContainer(),
            [
                'engine-1' => Reference::to('engine-2'),
                'engine-2' => Reference::to('engine-3'),
                'engine-3' => Reference::to('engine-1'),
            ]
        );

        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage(
            'Circular reference to "engine-2" detected while creating: engine-2, engine-3, engine-1.'
        );
        $factory->create('engine-1');
    }

    public function testCircularReference(): void
    {
        $factory = new Factory(new SimpleContainer(), [TreeItem::class => TreeItem::class]);

        $this->expectException(CircularReferenceException::class);
        $factory->create(TreeItem::class);
    }
}
