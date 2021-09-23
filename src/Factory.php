<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Contract\DefinitionInterface;
use Yiisoft\Definitions\Contract\ReferenceInterface;
use Yiisoft\Definitions\Infrastructure\DefinitionValidator;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotFoundException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Definitions\Infrastructure\Normalizer;

use function is_string;

/**
 * Factory allows creating objects passing arguments runtime.
 * A factory will try to use a PSR-11 compliant container to get dependencies,
 * but will fall back to manual instantiation
 * if the container cannot provide a required dependency.
 */
final class Factory
{
    private ?ContainerInterface $container;
    private FactoryContainer $factoryContainer;

    /**
     * @var bool $validate Validate definitions when set
     */
    private bool $validate;

    /**
     * Factory constructor.
     *
     * @psalm-param array<string, mixed> $definitions
     *
     * @throws InvalidConfigException
     */
    public function __construct(
        ContainerInterface $container = null,
        array $definitions = [],
        bool $validate = true
    ) {
        $this->container = $container;
        $this->factoryContainer = new FactoryContainer($container);
        $this->validate = $validate;
        $this->setMultiple($definitions);
    }

    /**
     * Creates a new object using the given configuration.
     *
     * You may view this method as an enhanced version of the `new` operator.
     * The method supports creating an object based on a class name, a configuration array or
     * an anonymous function.
     *
     * Below are some usage examples:
     *
     * ```php
     * // create an object using a class name
     * $object = $factory->create(\Yiisoft\Db\Connection::class);
     *
     * // create an object using a configuration array
     * $object = $factory->create([
     *     'class' => \Yiisoft\Db\Connection\Connection::class,
     *     '__construct()' => [
     *         'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     ],
     *     'setUsername()' => ['root'],
     *     'setPassword()' => [''],
     *     'setCharset()' => ['utf8'],
     * ]);
     * ```
     *
     * Using [[Container|dependency injection container]], this method can also identify
     * dependent objects, instantiate them and inject them into the newly created object.
     *
     * @param mixed $config The object configuration. This can be specified in one of the following forms:
     *
     * - A string: representing the class name of the object to be created.
     *
     * - A configuration array: the array must contain a `class` element which is treated as the object class,
     *   and the rest of the name-value pairs will be used to initialize the corresponding object properties.
     *
     * - A PHP callable: either an anonymous function or an array representing a class method
     *   (`[$class or $object, $method]`). The callable should return a new instance of the object being created.
     *
     * @throws InvalidConfigException If the configuration is invalid.
     * @throws CircularReferenceException
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return mixed|object The created object.
     *
     * @psalm-template T
     * @psalm-param mixed|class-string<T> $config
     * @psalm-return ($config is class-string ? T : mixed)
     * @psalm-suppress MixedReturnStatement
     */
    public function create($config)
    {
        if ($this->validate) {
            DefinitionValidator::validate($config);
        }

        if (is_string($config)) {
            if ($this->factoryContainer->hasDefinition($config)) {
                return $this->factoryContainer->get($config);
            }
            throw new NotFoundException($config);
        }

        $definition = $this->createDefinition($config);

        if ($definition instanceof ArrayDefinition) {
            $definition->setReferenceContainer($this->factoryContainer);
        }
        try {
            $container = ($this->container === null || $definition instanceof ReferenceInterface) ? $this->factoryContainer : $this->container;
            return $definition->resolve($container);
        } finally {
            if ($definition instanceof ArrayDefinition) {
                $definition->setReferenceContainer(null);
            }
        }
    }

    /**
     * Sets a definition to the factory.
     *
     * @param mixed $definition
     *
     * @throws InvalidConfigException
     */
    public function set(string $id, $definition): void
    {
        if ($this->validate) {
            DefinitionValidator::validate($definition, $id);
        }

        $this->factoryContainer->setDefinition($id, $definition);
    }

    /**
     * Sets multiple definitions at once.
     *
     * @param array $definitions definitions indexed by their ids
     *
     * @psalm-param array<string, mixed> $definitions
     *
     * @throws InvalidConfigException
     */
    public function setMultiple(array $definitions): void
    {
        /** @var mixed $definition */
        foreach ($definitions as $id => $definition) {
            $this->set($id, $definition);
        }
    }

    /**
     * @param mixed $config
     *
     * @throws InvalidConfigException
     */
    private function createDefinition($config): DefinitionInterface
    {
        $definition = Normalizer::normalize($config);

        if (
            ($definition instanceof ArrayDefinition) &&
            $this->factoryContainer->hasDefinition($definition->getClass()) &&
            ($containerDefinition = $this->factoryContainer->getDefinition($definition->getClass())) instanceof ArrayDefinition
        ) {
            $definition = $this->mergeDefinitions(
                $containerDefinition,
                $definition
            );
        }

        return $definition;
    }

    private function mergeDefinitions(DefinitionInterface $one, ArrayDefinition $two): DefinitionInterface
    {
        return $one instanceof ArrayDefinition ? $one->merge($two) : $two;
    }
}
