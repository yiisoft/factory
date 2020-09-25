<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Definitions\Normalizer;
use Yiisoft\Factory\Definitions\ArrayDefinition;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Factory\Exceptions\NotInstantiableException;

class Factory implements FactoryInterface
{
    /**
     * @var DefinitionInterface[] object definitions indexed by their types
     */
    private array $definitions = [];

    private Wrapper $wrapper;

    /**
     * Factory constructor.
     *
     * @param array $definitions
     * @param ContainerInterface $container
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function __construct(ContainerInterface $container = null, array $definitions = [])
    {
        $this->wrapper = new Wrapper($this, $container);
        $this->setMultiple($definitions);
    }

    public function create($config, array $params = [])
    {
        $definition = Normalizer::normalize($config, null, $params);
        if ($definition instanceof ArrayDefinition && $this->has($definition->getClass())) {
            $definition = $this->merge($this->getDefinition($definition->getClass()), $definition);
        }

        return $this->wrapper->resolve($definition);
    }

    private function merge(DefinitionInterface $one, DefinitionInterface $two): DefinitionInterface
    {
        return $one instanceof ArrayDefinition ? $one->merge($two) : $two;
    }

    public function getDefinition($id): DefinitionInterface
    {
        $definition = $this->has($id) ? $this->definitions[$id] : $id;

        return Normalizer::normalize($definition, $id);
    }

    /**
     * Sets a definition to the factory.
     * @param string $id
     * @param mixed $definition
     * @throws InvalidConfigException
     * @see `Normalizer::normalize()`
     */
    public function set(string $id, $definition): void
    {
        Normalizer::validate($definition);
        $this->definitions[$id] = $definition;
    }

    /**
     * Sets multiple definitions at once.
     * @param array $definitions definitions indexed by their ids
     * @throws InvalidConfigException
     */
    public function setMultiple(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            $this->set($id, $definition);
        }
    }

    public function has($id): bool
    {
        return isset($this->definitions[$id]);
    }
}
