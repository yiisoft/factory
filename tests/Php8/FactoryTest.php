<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Php8;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Factory\Tests\Support\SelfUnionType;
use Yiisoft\Factory\Tests\Support\VariadicUnionType;

final class FactoryTest extends TestCase
{
    public function dataCreateObjectWithVariadicUnionTypeInConstructor(): array
    {
        return [
            [[1, 'hello']],
            [[1]],
            [[]],
        ];
    }

    /**
     * @dataProvider dataCreateObjectWithVariadicUnionTypeInConstructor
     */
    public function testCreateObjectWithVariadicUnionTypeInConstructor(array $items): void
    {
        $object = (new Factory())->create([
            'class' => VariadicUnionType::class,
            '__construct()' => $items
        ]);

        $this->assertSame($items, $object->getItems());
    }

    public function testSelfUnionTypeDependency(): void
    {
        $factory = new Factory(
            null,
            [SelfUnionType::class => new SelfUnionType(new ColorPink())],
        );

        /** @var SelfUnionType $object */
        $object = $factory->create(SelfUnionType::class);

        $this->assertSame('pink', $object->getColor());
    }
}
