<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition\Decorator;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\DependencyResolverInterface;

final class LazyDefinitionDecorator implements DefinitionInterface
{
    private DefinitionInterface $definition;
    private string $objectClass;
    private LazyLoadingValueHolderFactory $factory;

    public function __construct(LazyLoadingValueHolderFactory $factory, DefinitionInterface $definition, string $objectClass)
    {
        $this->definition = $definition;
        $this->objectClass = $objectClass;
        $this->factory = $factory;
    }

    public function resolve(DependencyResolverInterface $container)
    {
        return $this->factory->createProxy(
            $this->objectClass,
            function (&$wrappedObject) use ($container) {
                $wrappedObject = $this->definition->resolve($container);
            }
        );
    }
}
