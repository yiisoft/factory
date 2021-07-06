<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Definition\Normalizer;
use Yiisoft\Factory\Definition\DefinitionValidator;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotInstantiableException;

class Factory implements FactoryInterface
{
    /**
     * @var ContainerInterface|null Parent container.
     */
    private ?ContainerInterface $container = null;

    /**
     * @var mixed[] Definitions
     * @psalm-var array<string, mixed>
     */
    private array $definitions = [];

    /**
     * @var DefinitionInterface[] object definitions indexed by their types
     * @psalm-var array<string, DefinitionInterface>
     */
    private array $definitionInstances = [];

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
     * @throws NotInstantiableException
     */
    public function __construct(
        ContainerInterface $container = null,
        array $definitions = [],
        bool $validate = true
    ) {
        $this->container = $container;
        $this->validate = $validate;
        $this->setDefaultDefinitions();
        $this->setMultiple($definitions);
    }

    public function create($config)
    {
        if ($this->validate) {
            DefinitionValidator::validate($config);
        }

        $definition = Normalizer::normalize($config);
        if (
            ($definition instanceof ArrayDefinition) &&
            $this->has($definition->getClass())
        ) {
            $definition = $this->merge(
                $this->getDefinition($definition->getClass()),
                $definition
            );
        }

        if ($definition instanceof ArrayDefinition) {
            return $definition->resolve($this->container ?? $this);
        }

        return $definition->resolve($this);
    }

    private function merge(DefinitionInterface $one, ArrayDefinition $two): DefinitionInterface
    {
        return $one instanceof ArrayDefinition ? $one->merge($two) : $two;
    }

    /**
     * @param string $id
     *
     * @throws NotInstantiableException
     *
     * @return mixed|object
     */
    public function get($id)
    {
        try {
            $definition = $this->getDefinition($id);
        } catch (InvalidConfigException $e) {
            throw new NotInstantiableException($id);
        }

        if ($definition instanceof ArrayDefinition) {
            return $definition->resolve($this->container ?? $this);
        }

        return $definition->resolve($this);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getDefinition(string $id): DefinitionInterface
    {
        if (!isset($this->definitionInstances[$id])) {
            if (isset($this->definitions[$id])) {
                $this->definitionInstances[$id] = Normalizer::normalize($this->definitions[$id], $id);
            } else {
                /** @psalm-var class-string $id */
                $this->definitionInstances[$id] = ArrayDefinition::fromPreparedData($id);
            }
        }

        return $this->definitionInstances[$id];
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

        $this->definitions[$id] = $definition;
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
     * Returns a value indicating whether the container has the definition of the specified name.
     *
     * @param string $id class name, interface name or alias name
     *
     * @return bool whether the container is able to provide instance of class specified.
     *
     * @see set()
     */
    public function has($id): bool
    {
        return isset($this->definitions[$id]);
    }

    private function setDefaultDefinitions(): void
    {
        /** @var ContainerInterface */
        $container = $this->container ?? $this;

        $this->setMultiple([
            ContainerInterface::class => $container,
        ]);
    }
}
