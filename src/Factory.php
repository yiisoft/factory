<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definition\DefinitionValidator;
use Yiisoft\Factory\Exception\InvalidConfigException;
use Yiisoft\Factory\Exception\NotFoundException;
use Yiisoft\Factory\Exception\NotInstantiableException;

class Factory implements FactoryInterface
{
    private DependencyResolver $container;

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
        $this->container = new DependencyResolver($this, $container);
        $this->validate = $validate;
        $this->setDefaultDefinitions();
        $this->setMultiple($definitions);
    }

    /**
     * @param mixed $config
     *
     * @throws InvalidConfigException
     * @throws NotFoundException
     * @throws NotInstantiableException
     *
     * @return mixed|object
     */
    public function create($config)
    {
        if ($this->validate) {
            DefinitionValidator::validate($config);
        }

        return $this->container
            ->createDefinition($config)
            ->resolve($this->container);
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

        $this->container->setFactoryDefinition($id, $definition);
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
     * @throws InvalidConfigException
     */
    private function setDefaultDefinitions(): void
    {
        $this->set(ContainerInterface::class, $this->container);
    }
}
