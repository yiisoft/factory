<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Php8;

use PHPUnit\Framework\TestCase;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Factory\Tests\Support\SelfUnionType;
use Yiisoft\Factory\Tests\Support\UnionBuiltInTypes;
use Yiisoft\Factory\Tests\Support\VariadicUnionType;
use Yiisoft\Test\Support\Container\SimpleContainer;

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
            '__construct()' => $items,
        ]);

        $this->assertSame($items, $object->getItems());
    }

    public function testSelfUnionTypeDependency(): void
    {
        $containerObject = new SelfUnionType(new ColorPink());

        $factory = new Factory(
            new SimpleContainer([SelfUnionType::class => $containerObject]),
        );

        /** @var SelfUnionType $object */
        $object = $factory->create(SelfUnionType::class);

        $this->assertSame('pink', $object->getColor());
    }

    public function testUnionBuiltInTypesDependency(): void
    {
        $factory = new Factory();

        $this->expectException(NotInstantiableException::class);
        $this->expectExceptionMessage(
            'Can not determine value of the "values" parameter of type "string|int" when instantiating ' .
            '"Yiisoft\Factory\Tests\Support\UnionBuiltInTypes::__construct()". ' .
            'Please specify argument explicitly.'
        );
        $factory->create(UnionBuiltInTypes::class);
    }
}
