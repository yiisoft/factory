<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Definition\Reference;
use Yiisoft\Factory\Definition\ValueDefinition;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotInstantiableException;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\Tests\Support\Car;
use Yiisoft\Factory\Tests\Support\Firefighter;
use Yiisoft\Factory\Tests\Support\ColorPink;
use Yiisoft\Factory\Tests\Support\Cube;
use Yiisoft\Factory\Tests\Support\EngineInterface;
use Yiisoft\Factory\Tests\Support\EngineMarkOne;
use Yiisoft\Factory\Tests\Support\EngineMarkTwo;
use Yiisoft\Factory\Tests\Support\GearBox;
use Yiisoft\Factory\Tests\Support\Immutable;
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
        $container = new SimpleContainer();
        $factory = new Factory($container, [EngineInterface::class => EngineMarkOne::class]);

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

    public function testGetByAlias(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container, [
            'engine' => EngineMarkOne::class,
        ]);

        $one = $factory->get('engine');
        $two = $factory->get('engine');

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one);
        $this->assertInstanceOf(EngineMarkOne::class, $two);
    }

    public function testTrivialDefinition(): void
    {
        $container = new SimpleContainer();
        $factory = new Factory($container);
        $factory->set(EngineMarkOne::class, EngineMarkOne::class);

        $one = $factory->get(EngineMarkOne::class);
        $two = $factory->get(EngineMarkOne::class);

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
            '__construct()' => [$factory->get(EngineMarkOne::class)],
        ]);
        $two = $factory->create([
            'class' => Car::class,
            '__construct()' => [$factory->get(EngineMarkTwo::class)],
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
            '__construct()' => ['engine' => $factory->get(EngineMarkOne::class)],
        ]);
        $two = $factory->create([
            'class' => Car::class,
            '__construct()' => ['engine' => $factory->get(EngineMarkTwo::class)],
        ]);

        $this->assertNotSame($one, $two);
        $this->assertInstanceOf(Car::class, $one);
        $this->assertInstanceOf(Car::class, $two);
        $this->assertInstanceOf(EngineMarkOne::class, $one->getEngine());
        $this->assertInstanceOf(EngineMarkTwo::class, $two->getEngine());
    }

    public function testCreateWithCallableValuesInConstructor(): void
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
        $object = $factory->get('recorder');

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

    public function testGetWithIncorrectConfiguration(): void
    {
        $factory = new Factory(null, ['x' => 42], false);

        $this->expectException(NotInstantiableException::class);
        $factory->get('x');
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
        $this->expectExceptionMessageMatches('/^Only references are allowed in parameters, a definition object was provided:/');
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
}
