<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit\Definition;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Tests\Support\Phone;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ArrayDefinitionTest extends TestCase
{
    public function testIntegerKeyOfArray(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: keys should be string.');
        ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => Phone::class,
            'RX',
        ]);
    }

    public function testClass(): void
    {
        $container = new SimpleContainer();

        $class = Phone::class;

        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => $class,
        ]);

        self::assertInstanceOf(Phone::class, $definition->resolve($container));
    }

    public function dataInvalidClass(): array
    {
        return [
            [42, 'Invalid definition: invalid class name "42".'],
            ['', 'Invalid definition: empty class name.'],
        ];
    }

    /**
     * @dataProvider dataInvalidClass
     */
    public function testInvalidClass($class, string $message): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage($message);
        ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
    }

    public function testWithoutClass(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: no class name specified.');
        ArrayDefinition::fromConfig([]);
    }

    public function dataConstructor(): array
    {
        return [
            [null, null, []],
            ['Kiradzu', null, ['Kiradzu']],
            ['Kiradzu', null, ['name' => 'Kiradzu']],
            ['Kiradzu', '2.0', ['Kiradzu', '2.0']],
            ['Kiradzu', '2.0', ['name' => 'Kiradzu', 'version' => '2.0']],
        ];
    }

    /**
     * @dataProvider dataConstructor
     */
    public function testConstructor(?string $name, ?string $version, array $constructorArguments): void
    {
        $container = new SimpleContainer();

        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => Phone::class,
            ArrayDefinition::CONSTRUCTOR => $constructorArguments,
        ]);

        /** @var Phone $phone */
        $phone = $definition->resolve($container);

        self::assertSame($name, $phone->getName());
        self::assertSame($version, $phone->getVersion());
    }

    public function testConstructorWithVariadicAndIntKeys(): void
    {
        $container = new SimpleContainer();

        $colors = ['red', 'green', 'blue'];
        $definition = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => Phone::class,
            ArrayDefinition::CONSTRUCTOR => [
                null,
                null,
                $colors[0],
                $colors[1],
                $colors[2],
            ],
        ]);

        /** @var Phone $phone */
        $phone = $definition->resolve($container);

        self::assertSame($colors, $phone->getColors());
    }

    public function testInvalidConstructor(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: incorrect constructor arguments. Expected array, got string.');
        ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => Phone::class,
            ArrayDefinition::CONSTRUCTOR => 'Kiradzu',
        ]);
    }

    public function dataSetProperties(): array
    {
        return [
            [false, null, []],
            [true, null, ['$dev' => true]],
            [true, 'Radar', ['$dev' => true, '$codeName' => 'Radar']],
        ];
    }

    /**
     * @dataProvider dataSetProperties
     */
    public function testSetProperties(bool $dev, ?string $codeName, array $setProperties): void
    {
        $container = new SimpleContainer();

        $definition = ArrayDefinition::fromConfig(array_merge([
            ArrayDefinition::CLASS_NAME => Phone::class,
        ], $setProperties));

        /** @var Phone $phone */
        $phone = $definition->resolve($container);

        self::assertSame($dev, $phone->dev);
        self::assertSame($codeName, $phone->codeName);
    }

    public function dataCallMethods(): array
    {
        return [
            [null, [], []],
            ['s43g23456', [], ['setId()' => ['s43g23456']]],
            ['777', [], ['setId777()' => []]],
            [
                '777',
                [['Browser', null]],
                [
                    'addApp()' => ['Browser'],
                    'setId777()' => [],
                ],
            ],
            [
                '42',
                [['Browser', '7']],
                [
                    'setId()' => ['42'],
                    'addApp()' => ['Browser', '7'],
                ],
            ],
            [
                null,
                [['Browser', '7']],
                [
                    'addApp()' => ['name' => 'Browser', 'version' => '7'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCallMethods
     */
    public function testCallMethods(?string $id, array $apps, array $callMethods): void
    {
        $container = new SimpleContainer();

        $definition = ArrayDefinition::fromConfig(array_merge(
            [
                ArrayDefinition::CLASS_NAME => Phone::class,
            ],
            $callMethods
        ));

        /** @var Phone $phone */
        $phone = $definition->resolve($container);

        self::assertSame($id, $phone->getId());
        self::assertSame($apps, $phone->getApps());
    }

    public function testCallFluentMethod(): void
    {
        $container = new SimpleContainer();

        $author = 'Sergei';
        $country = 'Russia';
        $definition = ArrayDefinition::fromConfig(
            array_merge([
                ArrayDefinition::CLASS_NAME => Phone::class,
            ], [
                'withAuthor()' => [$author],
                'withCountry()' => [$country],
            ])
        );

        /** @var Phone $phone */
        $phone = $definition->resolve($container);

        self::assertSame($author, $phone->getAuthor());
        self::assertSame($country, $phone->getCountry());
    }

    public function dataInvalidMethodCalls(): array
    {
        return [
            [['addApp()' => 'Browser'], 'Invalid definition: incorrect method arguments. Expected array, got string.'],
        ];
    }

    /**
     * @dataProvider dataInvalidMethodCalls
     */
    public function testInvalidMethodCalls($methodCalls, string $message): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage($message);
        ArrayDefinition::fromConfig(array_merge(
            [
                ArrayDefinition::CLASS_NAME => Phone::class,
            ],
            $methodCalls
        ));
    }

    public function testErrorOnMethodTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: key "setId" is not allowed. Did you mean "setId()" or "$setId"?');

        ArrayDefinition::fromConfig([
            'class' => Phone::class,
            'setId' => [42],
        ]);
    }

    public function testErrorOnPropertyTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: key "dev" is not allowed. Did you mean "dev()" or "$dev"?');
        ArrayDefinition::fromConfig([
            'class' => Phone::class,
            'dev' => true,
        ]);
    }

    public function testMerge(): void
    {
        $a = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => Phone::class,
            ArrayDefinition::CONSTRUCTOR => ['name' => 'Retro', 'version' => '1.0'],
            '$codeName' => 'a',
        ]);
        $b = ArrayDefinition::fromConfig([
            ArrayDefinition::CLASS_NAME => Phone::class,
            ArrayDefinition::CONSTRUCTOR => ['version' => '2.0'],
            '$dev' => true,
            '$codeName' => 'b',
            'setId()' => [42],
        ]);
        $c = $a->merge($b);

        $this->assertSame(Phone::class, $c->getClass());
        $this->assertSame(['name' => 'Retro', 'version' => '2.0'], $c->getConstructorArguments());
        $this->assertSame(
            [
                '$codeName' => [ArrayDefinition::FLAG_PROPERTY, '$codeName', 'b'],
                '$dev' => [ArrayDefinition::FLAG_PROPERTY, '$dev', true],
                'setId()' => [ArrayDefinition::FLAG_METHOD, 'setId()', [42]],
            ],
            $c->getMethodsAndProperties(),
        );
    }

    public function testMergeImmutability(): void
    {
        $a = ArrayDefinition::fromPreparedData(Phone::class);
        $b = ArrayDefinition::fromPreparedData(Phone::class);
        $c = $a->merge($b);
        $this->assertNotSame($a, $c);
        $this->assertNotSame($b, $c);
    }
}
