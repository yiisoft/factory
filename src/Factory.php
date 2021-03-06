<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\ArrayDefinition;
use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Definitions\Normalizer;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Factory\Exceptions\NotInstantiableException;

class Factory implements FactoryInterface
{
    /**
     * @var ContainerInterface parent container
     */
    private ?ContainerInterface $container = null;

    /**
     * @var DefinitionInterface[] object definitions indexed by their types
     */
    private array $definitions = [];

    /**
     * Factory constructor.
     *
     * @param array $definitions
     * @param ContainerInterface $container
     *
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function __construct(ContainerInterface $container = null, array $definitions = [])
    {
        $this->container = $container;
        $this->setMultiple($definitions);
    }

    public function create($config, array $params = [])
    {
        $definition = Normalizer::normalize($config, null, $params);
        if ($definition instanceof ArrayDefinition && $this->has($definition->getClass())) {
            $definition = $this->merge($this->getDefinition($definition->getClass()), $definition);
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

    public function get($id)
    {
        $definition = $this->getDefinition($id);
        if ($definition instanceof ArrayDefinition) {
            return $definition->resolve($this->container ?? $this);
        }

        return $definition->resolve($this);
    }

    /**
     * @param mixed $id
     */
    public function getDefinition($id): DefinitionInterface
    {
        if ($this->has($id)) {
            return $this->definitions[$id];
        }

        // prevent infinite loop when Reference definition points to string but not to a class
        if (\is_string($id)) {
            return ArrayDefinition::fromArray($id);
        }

        return Normalizer::normalize($id);
    }

    /**
     * Sets a definition to the factory.
     *
     * @param string $id
     * @param mixed $definition
     *
     * @throws InvalidConfigException
     *
     * @see `Normalizer::normalize()`
     */
    public function set(string $id, $definition): void
    {
        $this->definitions[$id] = Normalizer::normalize($definition, $id);
    }

    /**
     * Sets multiple definitions at once.
     *
     * @param array $definitions definitions indexed by their ids
     *
     * @throws InvalidConfigException
     */
    public function setMultiple(array $definitions): void
    {
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
}
