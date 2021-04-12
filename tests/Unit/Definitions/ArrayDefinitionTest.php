<?php

declare(strict_types=1);

namespace Definitions;

use PHPUnit\Framework\TestCase;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Tests\Support\Phone;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ArrayDefinitionTest extends TestCase
{
    public function testClass(): void
    {
        $container = new SimpleContainer();

        $class = Phone::class;

        $definition = new ArrayDefinition([
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
        $defintion = new ArrayDefinition([
            ArrayDefinition::CLASS_NAME => $class,
        ]);
    }

    public function testWithoutClass(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: no class name specified.');
        new ArrayDefinition([]);
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

        $definition = new ArrayDefinition([
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
        $definition = new ArrayDefinition([
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
        new ArrayDefinition([
            ArrayDefinition::CLASS_NAME => Phone::class,
            ArrayDefinition::CONSTRUCTOR => 'Kiradzu',
        ]);
    }

    public function dataSetProperties(): array
    {
        return [
            [false, null, []],
            [true, null, ['@dev' => true]],
            [true, 'Radar', ['@dev' => true, '@codeName' => 'Radar']],
        ];
    }

    /**
     * @dataProvider dataSetProperties
     */
    public function testSetProperties(bool $dev, ?string $codeName, array $setProperties): void
    {
        $container = new SimpleContainer();

        $definition = new ArrayDefinition(array_merge([
            ArrayDefinition::CLASS_NAME => Phone::class,], $setProperties));

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

        $definition = new ArrayDefinition(array_merge(
            [
                ArrayDefinition::CLASS_NAME => Phone::class,],
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
        $definition = new ArrayDefinition(
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
        new ArrayDefinition(array_merge(
            [
                ArrayDefinition::CLASS_NAME => Phone::class,],
            $methodCalls
        ));
    }

    public function testErrorOnMethodTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: metadata "setId" is not allowed. Did you mean "setId()" or "@setId"?');

        new ArrayDefinition([
            'class' => Phone::class,
            'setId' => [42],
        ]);
    }

    public function testErrorOnPropertyTypo(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: metadata "dev" is not allowed. Did you mean "dev()" or "@dev"?');
        new ArrayDefinition([
            'class' => Phone::class,
            'dev' => true,
        ]);
    }
}
