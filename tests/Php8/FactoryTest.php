<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Php8;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Factory;
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
        $object = (new Factory())->create(VariadicUnionType::class, $items);

        $this->assertSame($items, $object->getItems());
    }
}
