<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition\Decorator;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Psr\Container\ContainerInterface;
use Yiisoft\Definitions\Contract\DefinitionInterface;

final class LazyDefinitionDecorator implements DefinitionInterface
{
    public function __construct(private LazyLoadingValueHolderFactory $factory, private DefinitionInterface $definition, private string $objectClass)
    {
    }

    public function resolve(ContainerInterface $container): mixed
    {
        return $this->factory->createProxy(
            $this->objectClass,
            function (&$wrappedObject) use ($container) {
                $wrappedObject = $this->definition->resolve($container);
            }
        );
    }
}
