<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Contract\DefinitionInterface;
use Yiisoft\Definitions\Contract\DependencyResolverInterface;
use Yiisoft\Definitions\Contract\ReferenceInterface;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotFoundException;
use Yiisoft\Definitions\Exception\NotInstantiableClassException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Definitions\Infrastructure\Normalizer;

use function is_object;
use function is_string;

/**
 * @internal
 */
final class DependencyResolver implements ContainerInterface
{
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

    /**
     * @var array used to collect IDs instantiated during build to detect circular references
     *
     * @psalm-var array<string,1>
     */
    private array $creatingIds = [];

    public function __construct(?ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return mixed|object
     *
     * @psalm-suppress InvalidThrow
     */
    public function get($id)
    {
        if (isset($this->definitions[$id])) {
            return $this->getFromFactory($id);
        }

        if (class_exists($id)) {
            return $this->getFromFactory($id);
        }

        throw new NotInstantiableClassException($id);
    }

    public function has($id): bool
    {
        return isset($this->definitions[$id]) || ($this->container !== null && $this->container->has($id)) || class_exists($id);
    }

    /**
     * @param mixed $definition
     */
    public function setFactoryDefinition(string $id, $definition): void
    {
        $this->definitions[$id] = $definition;
    }

    /**
     * @param mixed $config
     *
     * @throws CircularReferenceException
     * @throws NotFoundException
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     *
     * @return mixed
     */
    public function create($config)
    {
        if (is_string($config)) {
            if ($this->canBeCreatedByFactory($config)) {
                return $this->getFromFactory($config);
            }
            throw new NotFoundException($config);
        }

        $definition = $this->createDefinition($config);

        if ($definition instanceof ArrayDefinition) {
            $definition->setReferenceContainer($this);
            $this->creatingIds[$definition->getClass()] = 1;
        }
        try {
            $container = $this->container === null || $definition instanceof ReferenceInterface ? $this : $this->container;
            return $definition->resolve($container);
        } finally {
            if ($definition instanceof ArrayDefinition) {
                unset($this->creatingIds[$definition->getClass()]);
                $definition->setReferenceContainer(null);
            }
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
     * @throws CircularReferenceException
     * @throws InvalidConfigException
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return mixed|object
     */
    private function getFromFactory(string $id)
    {
        if (isset($this->creatingIds[$id])) {
            throw new CircularReferenceException(sprintf(
                'Circular reference to "%s" detected while creating: %s.',
                $id,
                implode(',', array_keys($this->creatingIds))
            ));
        }

        $definition = $this->getDefinition($id);
        if ($definition instanceof ArrayDefinition) {
            $definition->setReferenceContainer($this);
        }
        $this->creatingIds[$id] = 1;
        try {
            $container = $this->container === null || $definition instanceof ReferenceInterface ? $this : $this->container;
            try {
                return $definition->resolve($container);
            } catch (NotFoundExceptionInterface $e) {
                return $definition->resolve($this);
            }
        } finally {
            unset($this->creatingIds[$id]);
            if ($definition instanceof ArrayDefinition) {
                $definition->setReferenceContainer(null);
            }
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function getDefinition(string $id): DefinitionInterface
    {
        if (!isset($this->definitionInstances[$id])) {
            if (isset($this->definitions[$id])) {
                if (is_object($this->definitions[$id]) && !($this->definitions[$id] instanceof ReferenceInterface)) {
                    return Normalizer::normalize(clone $this->definitions[$id], $id);
                }
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
