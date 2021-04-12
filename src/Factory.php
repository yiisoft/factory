<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Definition\Normalizer;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotInstantiableException;

use function is_string;

class Factory implements FactoryInterface
{
    /**
     * @var ContainerInterface|null Parent container.
     */
    private ?ContainerInterface $container = null;

    /**
     * @var DefinitionInterface[] object definitions indexed by their types
     * @psalm-var array<string, DefinitionInterface>
     */
    private array $definitions = [];

    /**
     * Factory constructor.
     *
     * @psalm-param array<string, mixed> $definitions
     *
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function __construct(ContainerInterface $container = null, array $definitions = [])
    {
        $this->container = $container;
        $this->setMultiple($definitions);
    }

    public function create($config, array $constructorArguments = [])
    {
        $definition = Normalizer::normalize($config, null, $constructorArguments);
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
     * @param mixed $id
     *
     * @throws InvalidConfigException
     */
    public function getDefinition($id): DefinitionInterface
    {
        if (is_string($id)) {
            // prevent infinite loop when Reference definition points to string but not to a class
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->definitions[$id] ?? new ArrayDefinition([ArrayDefinition::CLASS_NAME => $id]);
        }

        return Normalizer::normalize($id);
    }

    /**
     * Sets a definition to the factory.
     *
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
}
