<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Yiisoft\Factory\Definition\ArrayDefinition;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\Definition\Normalizer;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotFoundException;
use Yiisoft\Factory\Exception\NotInstantiableException;

use function is_object;
use function is_string;

/**
 * @internal
 */
final class FactoryContainer implements ContainerInterface
{
    private Factory $factory;
    private ?ContainerInterface $container;

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

    public function __construct(Factory $factory, ?ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * @param string $id
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return mixed|object
     *
     * @psalm-suppress InvalidThrow
     */
    public function get($id)
    {
        if ($this->container !== null) {
            return $this->container->get($id);
        }
        return $this->getFromFactory($id);
    }

    /**
     * @param string $id
     */
    public function has($id): bool
    {
        if ($this->container !== null) {
            return $this->container->has($id);
        }
        return $this->canBeCreatedByFactory($id);
    }

    /**
     * @param string $id
     *
     * @throws InvalidConfigException
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return mixed|object
     */
    public function getFromFactoryIfHasDefinition($id)
    {
        if (isset($this->definitions[$id])) {
            return $this->getFromFactory($id);
        }
        return $this->get($id);
    }

    /**
     * @param mixed $definition
     */
    public function setFactoryDefinition(string $id, $definition): void
    {
        $this->definitions[$id] = $definition;
    }

    public function isUsedFactory(): bool
    {
        return $this->container === null;
    }

    /**
     * @param mixed $config
     *
     * @throws InvalidConfigException
     */
    public function createDefinition($config): DefinitionInterface
    {
        if (is_string($config) && isset($this->definitions[$config])) {
            return Normalizer::normalize(
                is_object($this->definitions[$config])
                    ? clone $this->definitions[$config]
                    : $this->definitions[$config]
            );
        }

        $definition = Normalizer::normalize($config);

        if (
            ($definition instanceof ArrayDefinition) &&
            isset($this->definitions[$definition->getClass()])
        ) {
            $definition = $this->mergeDefinitions(
                $this->getDefinition($definition->getClass()),
                $definition
            );
        }

        return $definition;
    }

    /**
     * @param string $id
     *
     * @throws InvalidConfigException
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return mixed|object
     */
    private function getFromFactory($id)
    {
        return $this->getDefinition($id)->resolve($this);
    }

    /**
     * @throws InvalidConfigException
     */
    private function getDefinition(string $id): DefinitionInterface
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

    private function canBeCreatedByFactory(string $id): bool
    {
        return isset($this->definitions[$id]) || class_exists($id);
    }

    private function mergeDefinitions(DefinitionInterface $one, ArrayDefinition $two): DefinitionInterface
    {
        return $one instanceof ArrayDefinition ? $one->merge($two) : $two;
    }
}
