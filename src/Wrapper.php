<?php

declare(strict_types=1);

namespace Yiisoft\Factory;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Definitions\ArrayDefinition;
use Yiisoft\Factory\Definitions\DefinitionInterface;

class Wrapper implements ContainerInterface
{
    private Factory $factory;
    private ?ContainerInterface $container;

    public function __construct(Factory $factory, ContainerInterface $container = null)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    public function get($id)
    {
        return $this->resolve($this->factory->getDefinition($id));
    }

    public function has($id): bool
    {
        return $this->factory->has($id);
    }

    public function resolve(DefinitionInterface $definition)
    {
        $container = $definition instanceof ArrayDefinition ? $this->container : $this;

        return $definition->resolve($container ?? $this);
    }
}
