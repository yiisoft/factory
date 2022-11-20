<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition\Decorator;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Yiisoft\Factory\Definition\DefinitionInterface;
use Yiisoft\Factory\DependencyResolverInterface;

final class LazyDefinitionDecorator implements DefinitionInterface
{
    private DefinitionInterface $definition;

    public function __construct(private LazyLoadingValueHolderFactory $factory, DefinitionInterface $definition, private string $objectClass)
    {
        $this->definition = $definition;
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
