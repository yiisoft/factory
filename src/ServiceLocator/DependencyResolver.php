<?php

declare(strict_types=1);

namespace Yiisoft\Factory\ServiceLocator;

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
use Yiisoft\Injector\Injector;

use function is_object;

final class DependencyResolver implements DependencyResolverInterface
{
    private ?ContainerInterface $container;
    private ?Injector $injector = null;

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
     * @param string $id
     *
     * @throws CircularReferenceException
     * @throws InvalidConfigException
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return mixed|object
     */
    public function getFromServiceLocator(string $id)
    {
        if (isset($this->creatingIds[$id])) {
            throw new CircularReferenceException(sprintf(
                'Circular reference to "%s" detected while creating: %s.',
                $id,
                implode(',', array_keys($this->creatingIds))
            ));
        }

        $this->creatingIds[$id] = 1;
        try {
            return $this->getDefinition($id)->resolve($this);
        } finally {
            unset($this->creatingIds[$id]);
        }
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
        if ($this->container !== null && $this->container->has($id)) {
            return $this->container->get($id);
        }

        throw new NotInstantiableClassException($id);
    }

    /**
     * @param string $id
     */
    public function has($id): bool
    {
        return isset($this->definitions[$id]);
    }

    public function resolveReference(string $id)
    {
        return $this->getFromServiceLocator($id);
    }

    public function invoke(callable $callable)
    {
        return $this->getInjector()->invoke($callable);
    }

    /**
     * @param mixed $definition
     */
    public function set(string $id, $definition): void
    {
        $this->definitions[$id] = $definition;
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

    private function getInjector(): Injector
    {
        return $this->injector ??= new Injector($this);
    }
}
