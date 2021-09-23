<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Definitions\ValueDefinition;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotFoundException;
use Yiisoft\Definitions\Exception\NotInstantiableClassException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\CallableDependency;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\CarFactory;
use Yiisoft\Factory\Tests\Support\Circular\Chicken;
use Yiisoft\Factory\Tests\Support\Circular\CircularA;
use Yiisoft\Factory\Tests\Support\Circular\CircularB;
use Yiisoft\Factory\Tests\Support\Circular\TreeItem;
use Yiisoft\Factory\Tests\Support\ColorInterface;
use Yiisoft\Factory\Tests\Support\Circular\Egg;
use Yiisoft\Factory\Tests\Support\ExcessiveConstructorParameters;
use Yiisoft\Factory\Tests\Support\Firefighter;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Factory\Tests\Support\ColorRed;
use Yiisoft\Factory\Tests\Support\Cube;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\EngineMarkTwo;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Factory\Tests\Support\Immutable;
use Yiisoft\Factory\Tests\Support\InvokableCarFactory;
use Yiisoft\Factory\Tests\Support\MethodTest;
use Yiisoft\Factory\Tests\Support\NullableInterfaceDependency;
use Yiisoft\Factory\Tests\Support\NullableScalarConstructorArgument;
use Yiisoft\Factory\Tests\Support\Phone;
use Yiisoft\Factory\Tests\Support\PinkCircle;
use Yiisoft\Factory\Tests\Support\PropertyTest;
use Yiisoft\Factory\Tests\Support\Recorder;
use Yiisoft\Factory\Tests\Support\ScalarConstructorArgument;
use Yiisoft\Factory\Tests\Support\SelfType;
use Yiisoft\Factory\Tests\Support\TwoParametersDependency;
use Yiisoft\Factory\Tests\Support\VariadicClosures;
use Yiisoft\Factory\Tests\Support\VariadicConstructor;
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
        $factory = new Factory();

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
        $factory = new Factory();

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
        $factory = new Factory();

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
        ]);

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

    public function testDoNotFallbackToContainerForReferenceInConstructorOfArrayDefinition(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                EngineInterface::class => new EngineMarkOne(),
            ]),
            [
                EngineInterface::class => new EngineMarkTwo(),
                Car::class => [
                    '__construct()' => [
                        Reference::to(EngineInterface::class),
                    ],
                ],
            ]
        );

        $car = $factory->create(Car::class);
        $this->assertInstanceOf(EngineMarkTwo::class, $car->getEngine());
    }

    public function testDoNotFallbackToContainerForReferenceInConstructorOfArrayDefinitionInCreateMethod(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                EngineInterface::class => new EngineMarkOne(),
            ]),
            [
                EngineInterface::class => new EngineMarkTwo(),
            ]
        );

        $car = $factory->create([
            'class' => Car::class,
            '__construct()' => [
                Reference::to(EngineInterface::class),
            ],
        ]);
        $this->assertInstanceOf(EngineMarkTwo::class, $car->getEngine());
    }

    public function testDoNotFallbackToContainerForReferenceInMethodOfArrayDefinition(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                EngineInterface::class => new EngineMarkOne(),
                ColorInterface::class => new ColorRed(),
            ]),
            [
                ColorInterface::class => new ColorPink(),
                Car::class => [
                    'setColor()' => [
                        Reference::to(ColorInterface::class),
                    ],
                ],
            ]
        );

        $car = $factory->create(Car::class);
        $this->assertInstanceOf(ColorPink::class, $car->getColor());
    }

    public function testDoNotFallbackToContainerForReferenceInMethodOfArrayDefinitionInCreateMethod(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                EngineInterface::class => new EngineMarkOne(),
                ColorInterface::class => new ColorRed(),
            ]),
            [
                ColorInterface::class => new ColorPink(),
            ]
        );

        $car = $factory->create([
            'class' => Car::class,
            'setColor()' => [
                Reference::to(ColorInterface::class),
            ],
        ]);
        $this->assertInstanceOf(ColorPink::class, $car->getColor());
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

        $this->expectException(NotInstantiableClassException::class);
        $this->expectExceptionMessage('Can not instantiate ' . EngineInterface::class . '.');
        $factory->create('engine');
    }

    public function testResolveDependenciesFromFactory(): void
    {
        $factory = new Factory(null, [
            EngineInterface::class => [
                'class' => EngineMarkOne::class,
                'setNumber()' => [42],
            ],
        ]);

        $instance = $factory->create(Car::class);

        $this->assertInstanceOf(Car::class, $instance);
        $this->assertInstanceOf(EngineMarkOne::class, $instance->getEngine());
        $this->assertEquals(42, $instance->getEngine()->getNumber());
    }

    public function testCreateFactory(): void
    {
        $factory = new Factory(null, [
            'factoryObject' => [
                'class' => Factory::class,
                '__construct()' => [
                    'container' => new SimpleContainer(),
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
            '/^Only references are allowed in constructor arguments, a definition object was provided:/'
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
            '~^Only references are allowed in constructor arguments, a definition object was provided: ' .
            'Yiisoft\\\\Definitions\\\\ArrayDefinition::~'
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

    public function testShouldNotCreateUnspecifiedInterfaceWithoutContainer(): void
    {
        $factory = new Factory();

        $this->expectException(NotFoundException::class);
        $factory->create(ContainerInterface::class);
    }

    public function testShouldNotCreateUnspecifiedInterfaceWithContainer(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                ContainerInterface::class => new SimpleContainer(),
            ])
        );

        $this->expectException(NotFoundException::class);
        $factory->create(ContainerInterface::class);
    }

    public function testShouldCreateSpecifiedInterfaceWithoutContainer(): void
    {
        $factory = new Factory(null, [
            EngineInterface::class => new EngineMarkOne(),
        ]);

        $engine = $factory->create(EngineInterface::class);

        $this->assertInstanceOf(EngineMarkOne::class, $engine);
    }

    public function testCreateObjectWithDefinitionAndContainer(): void
    {
        $factory = new Factory(
            new SimpleContainer([
                EngineInterface::class => new EngineMarkOne(),
            ]),
            [
                EngineInterface::class => new EngineMarkTwo(),
            ]
        );

        $engine = $factory->create(EngineInterface::class);

        $this->assertInstanceOf(EngineMarkTwo::class, $engine);
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

        $this->assertSame(42, $engine->getNumber());
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

    public function dataVariadicConstructorParameters(): array
    {
        return [
            'stringIndexed' => [
                'first' => 1,
                'parameters' => 42,
                'second' => 43,
                'third' => 44,
            ],
            'integerIndexed' => [],
        ];
    }

    public function testVariadicConstructorStringIndexedParameters(): void
    {
        $factory = new Factory(
            null,
            [
                EngineInterface::class => EngineMarkOne::class,
                'test' => [
                    'class' => VariadicConstructor::class,
                    '__construct()' => [
                        'first' => 1,
                        'parameters' => 42,
                        'second' => 43,
                        'third' => 44,
                    ],
                ],
            ]
        );

        $object = $factory->create('test');

        $this->assertSame(1, $object->getFirst());
        $this->assertInstanceOf(EngineMarkOne::class, $object->getEngine());
        $this->assertSame([42, 43, 44], $object->getParameters());
    }

    public function testVariadicConstructorIntegerIndexedParameters(): void
    {
        $factory = new Factory(
            null,
            [
                EngineInterface::class => EngineMarkOne::class,
                'test' => [
                    'class' => VariadicConstructor::class,
                    '__construct()' => [
                        1,
                        new EngineMarkTwo(),
                        42,
                        43,
                        44,
                    ],
                ],
            ]
        );

        $object = $factory->create('test');

        $this->assertSame(1, $object->getFirst());
        $this->assertInstanceOf(EngineMarkTwo::class, $object->getEngine());
        $this->assertSame([42, 43, 44], $object->getParameters());
    }

    public function testClassProperties(): void
    {
        /** @var Phone $object */
        $object = (new Factory())->create([
            'class' => Phone::class,
            '$dev' => true,
        ]);

        $this->assertTrue($object->dev);
    }

    public function testClassMethods(): void
    {
        /** @var Phone $object */
        $object = (new Factory())->create([
            'class' => Phone::class,
            'setId()' => ['42'],
        ]);

        $this->assertSame('42', $object->getId());
    }

    public function testReferenceInConstructor(): void
    {
        $factory = new Factory(null, [
            'color' => ColorPink::class,
            Cube::class => [
                'class' => Cube::class,
                '__construct()' => [
                    Reference::to('color'),
                ],
            ],
        ]);

        $object = $factory->create(Cube::class);

        $this->assertInstanceOf(ColorPink::class, $object->getColor());
    }

    public function testDynamicReferenceInConstructor(): void
    {
        $factory = new Factory(null, [
            Cube::class => [
                'class' => Cube::class,
                '__construct()' => [
                    DynamicReference::to(ColorPink::class),
                ],
            ],
        ]);

        $object = $factory->create(Cube::class);

        $this->assertInstanceOf(ColorPink::class, $object->getColor());
    }

    public function testClosureInProperty(): void
    {
        $color = static fn (): ColorPink => new ColorPink();

        $factory = new Factory(
            null,
            [
                PropertyTest::class => [
                    'class' => PropertyTest::class,
                    '$property' => $color,
                ],
            ]
        );

        /** @var PropertyTest $object */
        $object = $factory->create(PropertyTest::class);

        $this->assertSame($color, $object->property);
    }

    public function testReferenceInProperty(): void
    {
        $factory = new Factory(
            null,
            [
                'color' => ColorPink::class,
                PropertyTest::class => [
                    'class' => PropertyTest::class,
                    '$property' => Reference::to('color'),
                ],
            ]
        );

        /** @var PropertyTest $object */
        $object = $factory->create(PropertyTest::class);

        $this->assertInstanceOf(ColorPink::class, $object->property);
    }

    public function testDynamicReferenceInProperty(): void
    {
        $color = new ColorPink();

        $factory = new Factory(
            null,
            [
                PropertyTest::class => [
                    'class' => PropertyTest::class,
                    '$property' => DynamicReference::to(static fn () => $color),
                ],
            ]
        );

        /** @var PropertyTest $object */
        $object = $factory->create(PropertyTest::class);

        $this->assertSame($color, $object->property);
    }

    public function testClosureInMethod(): void
    {
        $color = static fn (): ColorPink => new ColorPink();

        $factory = new Factory(
            null,
            [
                MethodTest::class => [
                    'class' => MethodTest::class,
                    'setValue()' => [$color],
                ],
            ]
        );

        /** @var MethodTest $object */
        $object = $factory->create(MethodTest::class);

        $this->assertSame($color, $object->getValue());
    }

    public function testReferenceInMethod(): void
    {
        $factory = new Factory(
            null,
            [
                'color' => ColorPink::class,
                MethodTest::class => [
                    'class' => MethodTest::class,
                    'setValue()' => [Reference::to('color')],
                ],
            ]
        );

        /** @var MethodTest $object */
        $object = $factory->create(MethodTest::class);

        $this->assertInstanceOf(ColorInterface::class, $object->getValue());
    }

    public function testDynamicReferenceInMethod(): void
    {
        $color = new ColorPink();

        $factory = new Factory(
            null,
            [
                MethodTest::class => [
                    'class' => MethodTest::class,
                    'setValue()' => [DynamicReference::to(static fn () => $color)],
                ],
            ]
        );

        /** @var MethodTest $object */
        $object = $factory->create(MethodTest::class);

        $this->assertSame($color, $object->getValue());
    }

    public function testAlias(): void
    {
        $factory = new Factory(
            null,
            [
                EngineInterface::class => Reference::to('engine'),
                'engine' => Reference::to('engine-mark-one'),
                'engine-mark-one' => EngineMarkOne::class,
            ]
        );

        $engine1 = $factory->create('engine-mark-one');
        $engine2 = $factory->create('engine');
        $engine3 = $factory->create(EngineInterface::class);

        $this->assertInstanceOf(EngineMarkOne::class, $engine1);
        $this->assertNotSame($engine1, $engine2);
        $this->assertNotSame($engine1, $engine3);
        $this->assertNotSame($engine2, $engine3);
    }

    public function testUndefinedDependencies(): void
    {
        $factory = new Factory(
            null,
            ['car' => Car::class]
        );

        $this->expectException(NotInstantiableClassException::class);
        $factory->create('car');
    }

    public function testDependencies(): void
    {
        $factory = new Factory(
            null,
            [
                'car' => Car::class,
                EngineInterface::class => EngineMarkTwo::class,
            ]
        );

        /** @var Car $car */
        $car = $factory->create('car');

        $this->assertInstanceOf(EngineMarkTwo::class, $car->getEngine());
    }

    public function testCallableDefinition(): void
    {
        $factory = new Factory(
            null,
            [
                EngineInterface::class => EngineMarkOne::class,
                'test' => static fn (EngineInterface $engine) => $engine,
            ]
        );

        $object = $factory->create('test');

        $this->assertInstanceOf(EngineMarkOne::class, $object);
    }

    public function testArrayStaticCallableDefinition(): void
    {
        $factory = new Factory(
            null,
            [
                EngineInterface::class => EngineMarkOne::class,
                'car' => [CarFactory::class, 'create'],
            ]
        );

        $car = $factory->create('car');

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(EngineMarkOne::class, $car->getEngine());
    }

    public function testArrayDynamicCallableDefinition(): void
    {
        $factory = new Factory(
            null,
            [
                ColorInterface::class => ColorPink::class,
                'car' => [CarFactory::class, 'createWithColor'],
            ]
        );

        $car = $factory->create('car');

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(ColorPink::class, $car->getColor());
    }

    public function testArrayDynamicCallableDefinitionWithObject(): void
    {
        $factory = new Factory(
            null,
            [
                ColorInterface::class => ColorPink::class,
                'car' => [new CarFactory(), 'createWithColor'],
            ]
        );

        $car = $factory->create('car');

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(ColorPink::class, $car->getColor());
    }

    public function testInvokableDefinition(): void
    {
        $factory = new Factory(
            null,
            [
                EngineInterface::class => EngineMarkOne::class,
                'invokable' => new InvokableCarFactory(),
            ]
        );

        $object = $factory->create('invokable');

        $this->assertInstanceOf(Car::class, $object);
    }

    public function testReferencesInArrayInDependencies(): void
    {
        $factory = new Factory(
            null,
            [
                'engine1' => EngineMarkOne::class,
                'engine2' => EngineMarkTwo::class,
                'engine3' => EngineMarkTwo::class,
                'engine4' => EngineMarkTwo::class,
                'car' => [
                    'class' => Car::class,
                    '__construct()' => [
                        Reference::to('engine1'),
                        [
                            'engine2' => Reference::to('engine2'),
                            'more' => [
                                'engine3' => Reference::to('engine3'),
                                'more' => [
                                    'engine4' => Reference::to('engine4'),
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $car = $factory->create('car');
        $this->assertInstanceOf(Car::class, $car);

        $moreEngines = $car->getMoreEngines();
        $this->assertInstanceOf(EngineMarkTwo::class, $moreEngines['engine2']);
        $this->assertInstanceOf(EngineMarkTwo::class, $moreEngines['more']['engine3']);
        $this->assertInstanceOf(EngineMarkTwo::class, $moreEngines['more']['more']['engine4']);
    }

    public function testCallableArrayValueInConstructor(): void
    {
        $array = [
            [EngineMarkTwo::class, 'getNumber'],
        ];

        $factory = new Factory(
            null,
            [
                EngineInterface::class => EngineMarkOne::class,
                Car::class => [
                    'class' => Car::class,
                    '__construct()' => [
                        Reference::to(EngineInterface::class),
                        $array,
                    ],
                ],
            ]
        );

        /** @var Car $object */
        $object = $factory->create(Car::class);

        $this->assertSame($array, $object->getMoreEngines());
    }

    public function testArrayDefinitionWithoutClass(): void
    {
        $factory = new Factory(
            null,
            [
                Firefighter::class => [
                    '__construct()' => ['Petr'],
                ],
            ],
        );

        $object = $factory->create(Firefighter::class);

        $this->assertSame('Petr', $object->getName());
    }

    /**
     * Factory don't should clone objects in constructor
     */
    public function testObjectInConstructor(): void
    {
        $color = new ColorPink();

        $factory = new Factory(
            null,
            [
                Cube::class => [
                    'class' => Cube::class,
                    '__construct()' => [$color],
                ],
            ],
        );

        $cube = $factory->create(Cube::class);

        $this->assertSame($color, $cube->getColor());
    }

    public function testDefinitionInterfaceAsDefinition(): void
    {
        $definition = ArrayDefinition::fromConfig([
            'class' => stdClass::class,
        ]);

        $this->expectException(InvalidConfigException::class);
        new Factory(null, ['test' => $definition]);
    }

    public function testDefinitionInterfaceAsDefinitionWithoutValidation(): void
    {
        $definition = ArrayDefinition::fromConfig([
            'class' => stdClass::class,
        ]);

        $factory = new Factory(null, ['test' => $definition], false);

        $this->expectException(InvalidConfigException::class);
        $factory->create('test');
    }

    public function testDefinitionInterfaceAsDefinitionInConstructorArguments(): void
    {
        $definition = [
            'class' => Cube::class,
            '__construct()' => [new ValueDefinition(new ColorPink())],
        ];

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches(
            '~^Only references are allowed in constructor arguments, a definition object was provided: ' .
            'Yiisoft\\\\Definitions\\\\ValueDefinition::~'
        );
        new Factory(null, ['test' => $definition]);
    }

    public function testDefinitionInterfaceAsDefinitionInConstructorArgumentsWithoutValidation(): void
    {
        $definition = [
            'class' => Cube::class,
            '__construct()' => [new ValueDefinition(new ColorPink())],
        ];

        $factory = new Factory(null, ['test' => $definition], false);

        $this->expectException(InvalidConfigException::class);
        $factory->create('test');
    }

    public function testOptionalCircularClassDependency(): void
    {
        $factory = new Factory();

        $a = $factory->create(CircularA::class);

        $this->assertInstanceOf(CircularB::class, $a->b);
        $this->assertNull($a->b->a);
    }

    public function testCircularClassDependency(): void
    {
        $factory = new Factory(
            null,
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
        $factory = new Factory();

        $this->expectException(CircularReferenceException::class);
        $factory->create(Chicken::class);
    }

    public function testCircularReferences(): void
    {
        $factory = new Factory(
            null,
            [
                'engine-1' => Reference::to('engine-2'),
                'engine-2' => Reference::to('engine-3'),
                'engine-3' => Reference::to('engine-1'),
            ]
        );

        $this->expectException(CircularReferenceException::class);
        $factory->create('engine-1');
    }

    public function testCircularReference(): void
    {
        $factory = new Factory();

        $this->expectException(CircularReferenceException::class);
        $factory->create(TreeItem::class);
    }

    /**
     * @link https://github.com/yiisoft/di/pull/189
     */
    public function testFalsePositiveCircularReferenceWithClassID(): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new Factory();

        // Build an object
        $factory->create(ColorPink::class);

        // set definition to container
        (fn (string $id, $definition) => $this->set($id, $definition))->call(
            $factory,
            ColorPink::class,
            ColorPink::class
        );

        try {
            $factory->create(ColorPink::class);
        } catch (CircularReferenceException $e) {
            $this->fail('Circular reference detected false positively.');
        }
    }

    /**
     * @link https://github.com/yiisoft/di/pull/189
     */
    public function testFalsePositiveCircularReferenceWithStringID(): void
    {
        $this->expectNotToPerformAssertions();

        $factory = new Factory();
        try {
            $factory->create('test');
        } catch (NotFoundException $e) {
            // It is expected
        }

        // set definition to container
        (fn (string $id, $definition) => $this->set($id, $definition))->call($factory, 'test', ColorPink::class);

        try {
            $factory->create('test');
        } catch (CircularReferenceException $e) {
            $this->fail('Circular reference detected false positively.');
        }
    }

    public function testNullableScalarConstructorArgument(): void
    {
        $factory = new Factory();

        $object = $factory->create(NullableScalarConstructorArgument::class);

        $this->assertNull($object->getName());
    }

    public function testScalarConstructorArgument(): void
    {
        $factory = new Factory();

        $this->expectException(NotInstantiableException::class);
        $this->expectExceptionMessage(
            'Can not determine value of the "name" parameter of type "string" when instantiating ' .
            '"Yiisoft\Factory\Tests\Support\ScalarConstructorArgument::__construct()". ' .
            'Please specify argument explicitly.'
        );
        $factory->create(ScalarConstructorArgument::class);
    }

    public function testCreateWithDependecyNonExistInContainer(): void
    {
        $factory = new Factory(new SimpleContainer(), []);

        $circle = $factory->create(PinkCircle::class);

        $this->assertInstanceOf(ColorPink::class, $circle->getColor());
    }
}
