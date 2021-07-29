<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Definition\Reference;
use Yiisoft\Factory\Definition\ValueDefinition;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotFoundException;
use Yiisoft\Factory\Exception\NotInstantiableException;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\CallableDependency;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\ExcessiveConstructorParameters;
use Yiisoft\Factory\Tests\Support\Firefighter;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Factory\Tests\Support\Cube;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\EngineMarkTwo;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Factory\Tests\Support\Immutable;
use Yiisoft\Factory\Tests\Support\NullableInterfaceDependency;
use Yiisoft\Factory\Tests\Support\Phone;
use Yiisoft\Factory\Tests\Support\Recorder;
use Yiisoft\Factory\Tests\Support\SelfType;
use Yiisoft\Factory\Tests\Support\TwoParametersDependency;
use Yiisoft\Factory\Tests\Support\VariadicClosures;
use Yiisoft\Test\Support\Container\SimpleContainer;

use function count;

final class FactoryTest extends TestCase
{
    public function testCanCreateByAlias(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, ['engine' => EngineMarkOne::class]);

        $one = $factory->create('engine');
        $two = $factory->create('engine');

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testCanCreateByInterfaceAsStringDefinition(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [EngineInterface::class => EngineMarkOne::class]);

        $one = $factory->create(EngineInterface::class);
        $two = $factory->create(EngineInterface::class);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testCanCreateByInterfaceAsReferenceDefinition(): void
    {
        $factory = new Factory(null, [EngineInterface::class => EngineMarkOne::class]);

        $one = $factory->create(Reference::to(EngineInterface::class));
        $two = $factory->create(Reference::to(EngineInterface::class));

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * Factory should always return new instance even if an object is set to it.
     * In this case it is being cloned.
     */
    public function testObjectIsCloned(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, ['engine' => new EngineMarkOne()]);

        $one = $factory->create('engine');
        $two = $factory->create('engine');

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * If class name is used as ID, factory must try create given class.
     */
    public function testCreateClassNotDefinedInConfig(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $one = $factory->create(EngineMarkOne::class);
        $two = $factory->create(EngineMarkOne::class);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    /**
     * Configuration specified in {@see Factory::create()} should be merged with configuration stored in a factory.
     */
    public function testMergeFactoryConfig(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [
            EngineMarkOne::class => [
                'class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create([
            'class' => EngineMarkOne::class,
        ]);

        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertEquals(42, $instance->getNumber());
    }

    /**
     * Configuration specified in {@see Factory::create()} has more priority than configuration stored in a factory.
     */
    public function testOverrideFactoryConfig(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [
            EngineMarkOne::class => [
                'class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create([
            'class' => EngineMarkOne::class,
            'setNumber()' => [43],
        ]);

        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertEquals(43, $instance->getNumber());
    }

    public function testTrivialDefinition(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);
        $factory->set(EngineMarkOne::class, EngineMarkOne::class);

        $one = $factory->create(EngineMarkOne::class);
        $two = $factory->create(EngineMarkOne::class);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testCreateWithConstructor(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $one = $factory->create([
            'class' => Car::class,
            '__construct()' => [$factory->create(EngineMarkOne::class)],
        ]);
        $two = $factory->create([
            'class' => Car::class,
            '__construct()' => [$factory->create(EngineMarkTwo::class)],
        ]);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one->getEngine());
        $this->assertInstanceOf(EngineMarkTwo::class, $two->getEngine());
    }

    public function testCreateWithNamedParametersInConstructor(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $one = $factory->create([
            'class' => Car::class,
            '__construct()' => ['engine' => $factory->create(EngineMarkOne::class)],
        ]);
        $two = $factory->create([
            'class' => Car::class,
            '__construct()' => ['engine' => $factory->create(EngineMarkTwo::class)],
        ]);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one->getEngine());
        $this->assertInstanceOf(EngineMarkTwo::class, $two->getEngine());
    }

    public function testCreateWithScalarParametersInConstructor(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $object = $factory->create([
            'class' => TwoParametersDependency::class,
            '__construct()' => [
                'firstParameter' => 'date',
                'secondParameter' => 'time',
            ],
        ], );

        $this->assertInstanceOf(TwoParametersDependency::class, $object);
        $this->assertSame('date', $object->getFirstParameter());
        $this->assertSame('time', $object->getSecondParameter());
    }

    public function testCreateWithCallableParametersInConstructor(): void
    {
        $factory = new Factory();

        $object = $factory->create([
            'class' => CallableDependency::class,
            '__construct()' => [
                'callback' => static fn () => 42,
            ],
        ]);

        $this->assertInstanceOf(CallableDependency::class, $object);
        $this->assertSame(42, $object->get());
    }

    public function testCreateWithInvalidParametersInCosntructor(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $this->expectException(InvalidConfigException::class);

        $factory->create([
            'class' => TwoParametersDependency::class,
            '__construct()' => ['firstParam' => 'param1', 1 => 'param2'],
        ]);
    }

    public function testCreateWithRandomOrderedParametersInConstructor(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);

        $object = $factory->create([
            'class' => TwoParametersDependency::class,
            '__construct()' => [1 => 'param2', 0 => 'param1'],
        ]);

        $this->assertInstanceOf(TwoParametersDependency::class, $object);
        $this->assertSame('param1', $object->getFirstParameter());
        $this->assertSame('param2', $object->getSecondParameter());
    }

    /**
     * In case class to be created has dependencies, these are looked for in DI container.
     */
    public function testResolveDependenciesUsingContainer(): void
    {
        $origin = new EngineMarkOne();
        $container = new SimpleContainer([EngineInterface::class => $origin]);
        $factory = new Factory($container);

        $one = $factory->create(Car::class);
        $two = $factory->create(Car::class);

        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $two->getEngine());
        $this->assertNotSame($one, $two);
        $this->assertNotSame($origin, $one);
        $this->assertNotSame($origin, $two);
        $this->assertSame($one->getEngine(), $two->getEngine());
    }

    /**
     * Falling back to container is not desired because it would likely result to implicitly returning the
     * same instance of the object when calling {@see Factory::create()} multiple times with the same ID.
     */
    public function testDoNotFallbackToContainer(): void
    {
        $engine = new EngineMarkOne();
        $engine->setNumber(42);
        $container = new SimpleContainer([EngineMarkOne::class => $engine]);
        $factory = new Factory($container);

        $instance = $factory->create(EngineMarkOne::class);

        $this->assertInstanceOf(EngineMarkOne::class, $instance);
        $this->assertNotEquals(42, $instance->getNumber());
        $this->assertNotSame($engine, $instance);
    }

    public function testDoNotFallbackToContainerForReference(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                EngineInterface::class => new EngineMarkOne(),
            ]),
            [
                EngineInterface::class => new EngineMarkTwo(),
                'engine' => Reference::to(EngineInterface::class),
            ]
        );

        $engine = $factory->create('engine');
        $this->assertInstanceOf(EngineMarkTwo::class, $engine);
    }

    public function testExceptionAndDoNotFallbackToContainerForReference(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                EngineInterface::class => new EngineMarkOne(),
            ]),
            [
                'engine' => Reference::to(EngineInterface::class),
            ]
        );

        $this->expectException(NotInstantiableException::class);
        $this->expectExceptionMessage('Can not instantiate ' . EngineInterface::class . '.');
        $factory->create('engine');
    }

    /**
     * When resolving dependencies, factory should rely on container only
     */
    public function testDoNotResolveDependenciesFromFactory(): void
    {
        $container = new SimpleContainer([EngineInterface::class => new EngineMarkOne()]);
        $factory = new Factory($container, [
            EngineInterface::class => [
                'class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create(Car::class);

        $this->assertInstanceOf(Car::class, $instance);
        $this->assertInstanceOf(EngineMarkOne::class, $instance->getEngine());
        $this->assertEquals(0, $instance->getEngine()->getNumber());
    }

    public function testCreateFactory(): void
    {
        $container = new SimpleContainer([ContainerInterface::class => &$container]);
        $factory = new Factory($container, [
            'factoryObject' => [
                'class' => Factory::class,
                '__construct()' => [
                    'container' => Reference::to(ContainerInterface::class),
                    'definitions' => [],
                ],
            ],
        ]);

        $oneFactoryObject = $factory->create('factoryObject');
        $otherFactoryObject = $factory->create('factoryObject');

        $this->assertNotSame($oneFactoryObject, $otherFactoryObject);
        $this->assertNotSame($oneFactoryObject, $factory);
        $this->assertInstanceOf(Factory::class, $oneFactoryObject);
        $this->assertInstanceOf(Factory::class, $otherFactoryObject);
    }

    public function testCreateFactoryImmutable(): void
    {
        $factory = new Factory(new SimpleContainer(), [
            'immutableObject' => [
                'class' => Immutable::class,
                'id()' => ['id-testMe'],
                'fieldImmutable()' => ['testMe'],
            ],
        ]);

        $oneImmutableObject = $factory->create('immutableObject');
        $otherImmutableObject = (new Immutable())->fieldImmutable('testMe');
        $otherImmutableObject->id('id-testMe');

        $this->assertEquals($oneImmutableObject, $otherImmutableObject);
    }

    public function testCallsOrder(): void
    {
        $factory = new Factory(new SimpleContainer(), [
            'recorder' => [
                'class' => Recorder::class,
                'first()' => [],
                '$second' => null,
                'third()' => [],
                '$fourth' => null,
            ],
        ]);

        /** @var Recorder $object */
        $object = $factory->create('recorder');

        $this->assertEquals(['Call first()', 'Set @second', 'Call third()', 'Set @fourth'], $object->getEvents());
    }

    public function dataCreate(): array
    {
        return [
            [
                'Hello World',
                '2.0',
                [ArrayDefinition::CONSTRUCTOR => ['name' => 'Hello World', 'version' => '1.0']],
                [
                    ArrayDefinition::CLASS_NAME => Phone::class,
                    ArrayDefinition::CONSTRUCTOR => ['version' => '2.0'],
                ],
            ],
            [
                'Table',
                '1.0',
                Phone::class,
                [
                    ArrayDefinition::CLASS_NAME => Phone::class,
                    ArrayDefinition::CONSTRUCTOR => ['name' => 'Table', 'version' => '1.0'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCreate
     */
    public function testCreate(
        $expectedName,
        $expectedVersion,
        $factoryDefinition,
        $createDefinition
    ): void {
        $factory = new Factory(null, [
            Phone::class => $factoryDefinition,
        ]);

        $phone = $factory->create($createDefinition);

        $this->assertInstanceOf(Phone::class, $phone);
        $this->assertSame($expectedName, $phone->getName());
        $this->assertSame($expectedVersion, $phone->getVersion());
    }

    public function dataCreateObjectWithVariadicClosuresInConstructor(): array
    {
        return [
            [[fn () => '1'], '1'],
            [[fn () => '1', fn () => '2'], '12'],
            [[], ''],
        ];
    }

    /**
     * @dataProvider dataCreateObjectWithVariadicClosuresInConstructor
     */
    public function testCreateObjectWithVariadicClosuresInConstructor(array $closures, string $expectedConcat): void
    {
        $object = (new Factory())->create([
            'class' => VariadicClosures::class,
            '__construct()' => $closures,
        ]);

        $concat = '';
        foreach ($object->getClosures() as $c) {
            $concat .= $c();
        }

        $this->assertCount(count($closures), $object->getClosures());
        $this->assertSame($expectedConcat, $concat);
    }

    public function testCreateWithInvalidFactoryDefinitionWithoutValidation(): void
    {
        $factory = new Factory(null, ['x' => 42], false);

        $this->expectException(InvalidConfigException::class);
        $factory->create('x');
    }

    public function testSelfTypeDependency(): void
    {
        $containerObject = new SelfType();
        $containerObject->setColor('pink');

        $factory = new Factory(
            new SimpleContainer([SelfType::class => $containerObject]),
        );

        /** @var SelfType $object */
        $object = $factory->create(['class' => SelfType::class]);

        $this->assertSame('pink', $object->getColor());
    }

    public function testCreateFromCallable(): void
    {
        $object = (new Factory())->create([$this, 'createStdClass']);

        $this->assertInstanceOf(stdClass::class, $object);
    }

    public function createStdClass(): stdClass
    {
        return new stdClass();
    }

    public function testCreateWithInvalidConfig(): void
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: 42');
        $factory->create(42);
    }

    public function testDefinitionInConstructor(): void
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches(
            '/^Only references are allowed in parameters, a definition object was provided:/'
        );
        $factory->create([
            'class' => Car::class,
            '__construct()' => [
                new ValueDefinition(new EngineMarkTwo(), 'object'),
            ],
        ]);
    }

    public function testSetMultiple(): void
    {
        $factory = new Factory();

        $factory->setMultiple([
            'object1' => [$this, 'createStdClass'],
            'object2' => GearBox::class,
        ]);

        $this->assertInstanceOf(stdClass::class, $factory->create('object1'));
        $this->assertInstanceOf(GearBox::class, $factory->create('object2'));
    }

    public function testSetInvalidDefinition(): void
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $factory->set('test', 42);
    }

    public function testDefinitionAsConstructorArgument(): void
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches(
            '~^Only references are allowed in parameters, a definition object was provided: ' .
            'Yiisoft\\\\Factory\\\\Definition\\\\ArrayDefinition::~'
        );
        $factory->create([
            'class' => Cube::class,
            '__construct()' => [ArrayDefinition::fromConfig(['class' => ColorPink::class])],
        ]);
    }

    public function testCreateWithInvalidDefinitionWithValidation(): void
    {
        $factory = new Factory();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Invalid definition: invalid key in array definition. Allow only string keys, got 0.'
        );
        $factory->create([stdClass::class]);
    }

    public function testCreateWithInvalidDefinitionWithoutValidation(): void
    {
        $factory = new Factory(null, [], false);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid definition: 42');
        $factory->create(42);
    }

    public function testCreateObjectWithNullableStringConstructorArgument(): void
    {
        $factory = new Factory();

        $object = $factory->create(Firefighter::class);

        $this->assertNull($object->getName());
    }

    public function testCreateNonExistsClass(): void
    {
        $factory = new Factory();

        $this->expectException(NotFoundException::class);
        $factory->create('NonExistsClass');
    }

    public function testContainerInterfaceWithFactory(): void
    {
        $factory = new Factory(null, ['x' => new stdClass()]);

        $container = $factory->create(ContainerInterface::class);

        $this->assertTrue($container->has('x'));
        $this->assertFalse($container->has('y'));
    }

    public function testContainerInterfaceWithContainer(): void
    {
        $factory = new Factory(
            new SimpleContainer(['x' => new stdClass()])
        );

        $container = $factory->create(ContainerInterface::class);

        $this->assertTrue($container->has('x'));
        $this->assertFalse($container->has('y'));
    }

    public function testDefinitionEqualId(): void
    {
        $factory = new Factory(
            null,
            [
                EngineInterface::class => EngineMarkOne::class,
                EngineMarkOne::class => [
                    'class' => EngineMarkOne::class,
                    '__construct()' => [42],
                ],
            ]
        );

        /** @var EngineMarkOne $engine */
        $engine = $factory->create(EngineInterface::class);

        $this->assertSame(0, $engine->getNumber());
    }

    public function testOptionalInterfaceDependency(): void
    {
        $factory = new Factory();

        $object = $factory->create(NullableInterfaceDependency::class);

        $this->assertNull($object->getEngine());
    }

    public function testOptionalInterfaceDependencyWithDefiniion(): void
    {
        $factory = new Factory(null, [EngineInterface::class => EngineMarkOne::class]);

        $object = $factory->create(NullableInterfaceDependency::class);

        $this->assertInstanceOf(EngineMarkOne::class, $object->getEngine());
    }

    public function testIntegerIndexedConstructorArguments(): void
    {
        $factory = new Factory(
            null,
            [
                'items' => [
                    'class' => ArrayIterator::class,
                    '__construct()' => [
                        [],
                        ArrayIterator::STD_PROP_LIST,
                    ],
                ],
            ]
        );

        $items = $factory->create('items');

        $this->assertInstanceOf(ArrayIterator::class, $items);
        $this->assertSame(ArrayIterator::STD_PROP_LIST, $items->getFlags());
    }

    public function testExcessiveConstructorParametersIgnored(): void
    {
        $factory = new Factory(
            null,
            [
                'test' => [
                    'class' => ExcessiveConstructorParameters::class,
                    '__construct()' => [
                        'parameter' => 'Mike',
                        'age' => 43,
                    ],
                ],
            ]
        );

        $object = $factory->create('test');

        $this->assertSame(['Mike'], $object->getAllParameters());
    }
}
