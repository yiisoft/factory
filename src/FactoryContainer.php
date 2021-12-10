<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use LogicException;
use Psr\Container\ContainerInterface;
use Yiisoft\Definitions\ArrayDefinition;
use Yiisoft\Definitions\Contract\DefinitionInterface;
use Yiisoft\Definitions\Contract\ReferenceInterface;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotInstantiableClassException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Definitions\Helpers\Normalizer;

use function array_key_exists;
use function is_object;
use function is_string;

/**
 * Factory's primary container.
 *
 * @internal
 */
final class FactoryContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface Container to use for resolving dependencies.
     */
    private ContainerInterface $container;

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
     * @param ContainerInterface $container Container to use for resolving dependencies.
     */
    public function __construct(ContainerInterface $container)
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
        if ($this->hasDefinition($id)) {
            return $this->build($id);
        }

        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        throw new NotInstantiableClassException($id);
    }

    public function has($id): bool
    {
        return $this->hasDefinition($id) || $this->container->has($id);
    }

    /**
     * @return mixed
     */
    public function create(DefinitionInterface $definition)
    {
        if ($definition instanceof ArrayDefinition) {
            $this->creatingIds[$definition->getClass()] = 1;
        }

        try {
            return $definition->resolve($this);
        } finally {
            if ($definition instanceof ArrayDefinition) {
                unset($this->creatingIds[$definition->getClass()]);
            }
        }
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

                if (
                    is_string($this->definitions[$id])
                    && $this->hasDefinition($this->definitions[$id])
                    && $this->definitions[$id] !== $this->definitions[$this->definitions[$id]]
                ) {
                    return $this->getDefinition($this->definitions[$id]);
                }

                $this->definitionInstances[$id] = Normalizer::normalize(
                    is_string($this->definitions[$id]) && class_exists($this->definitions[$id])
                        ? ['class' => $this->definitions[$id]]
                        : $this->definitions[$id],
                    $id
                );
            } else {
                throw new LogicException(
                    sprintf('No definition found for "%s".', $id)
                );
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
        return array_key_exists($id, $this->definitions);
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
                implode(', ', array_keys($this->creatingIds))
            ));
        }

        $definition = $this->getDefinition($id);

        $this->creatingIds[$id] = 1;
        try {
            return $definition->resolve($this);
        } finally {
            unset($this->creatingIds[$id]);
        }
    }
}
