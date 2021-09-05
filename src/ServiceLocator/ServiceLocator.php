<?php

declare(strict_types=1);

namespace Yiisoft\Factory\ServiceLocator;

use Psr\Container\ContainerInterface;
use Yiisoft\Definitions\Infrastructure\DefinitionValidator;

final class ServiceLocator implements ContainerInterface
{
    private DependencyResolver $dependencyResolver;

    public function __construct(
        ContainerInterface $container = null,
        array $definitions = [],
        bool $validate = true
    ) {
        $this->dependencyResolver = new DependencyResolver($container);

        foreach ($definitions as $id => $definition) {
            if ($validate) {
                DefinitionValidator::validate($definition, $id);
            }
            $this->dependencyResolver->set($id, $definition);
        }
    }

    public function get($id)
    {
        return $this->dependencyResolver->getFromServiceLocator($id);
    }

    public function has($id): bool
    {
        return $this->dependencyResolver->has($id);
    }
}
