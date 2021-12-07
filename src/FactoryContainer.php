<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Contract\DefinitionInterface;
use Yiisoft\Definitions\Contract\ReferenceInterface;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotInstantiableClassException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Definitions\Helpers\Normalizer;

use function is_object;

/**
 * Factory's primary container.
 *
 * @internal
 */
final class FactoryContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface|null Container to use for resolving dependencies. When null, only definitions
     * are used.
     */
    private ?ContainerInterface $container;

    /**
     * @var array Definitions to create objects with.
     * @psalm-var array<string, mixed>
     */
    private array $definitions = [];

    /**
     * @var DefinitionInterface[] Object created from definitions indexed by their types.
     * @psalm-var array<string, DefinitionInterface>
     */
    private array $definitionInstances = [];

    /**
     * @var array Used to collect IDs instantiated during build to detect circular references.
     *
     * @psalm-var array<string,1>
     */
    private array $creatingIds = [];

    /**
     * @param ContainerInterface|null $container Container to use for resolving dependencies. When null, only definitions
     * are used.
     */
    public function __construct(?ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     *
     * @param string $id
     *
     * @return mixed|object
     * @psalm-suppress InvalidThrow
     */
    public function get($id)
    {
        if (isset($this->definitions[$id]) || class_exists($id)) {
            return $this->build($id);
        }

        if ($this->container !== null && $this->container->has($id)) {
            return $this->container->get($id);
        }

        throw new NotInstantiableClassException($id);
    }

    public function has($id): bool
    {
        return isset($this->definitions[$id]) || ($this->container !== null && $this->container->has($id)) || class_exists($id);
    }

    /**
     * Get definition by identifier provided.
     *
     * @throws InvalidConfigException
     */
    public function getDefinition(string $id): DefinitionInterface
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

    /**
     * Check if there is a definition with a given identifier.
     *
     * @param string $id Identifier to look for.
     *
     * @return bool If there is a definition with a given identifier.
     */
    public function hasDefinition(string $id): bool
    {
        if (isset($this->definitions[$id])) {
            return true;
        }

        if (class_exists($id)) {
            $this->definitions[$id] = $id;
            return true;
        }

        return false;
    }

    /**
     * Set definition for a given identifier.
     *
     * @param string $id Identifier to set definition for.
     * @param mixed $definition Definition to set.
     */
    public function setDefinition(string $id, $definition): void
    {
        $this->definitions[$id] = $definition;
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
    private function build(string $id)
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
            $container = ($this->container === null || $definition instanceof ReferenceInterface) ? $this : $this->container;
            try {
                return $definition->resolve($container);
            } catch (NotFoundExceptionInterface $e) {
                if ($container === $this) {
                    throw $e;
                }

                return $definition->resolve($this);
            }
        } finally {
            unset($this->creatingIds[$id]);
            if ($definition instanceof ArrayDefinition) {
                $definition->setReferenceContainer(null);
            }
        }
    }
}
