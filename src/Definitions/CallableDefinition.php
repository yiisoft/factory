<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Injector\Injector;

class CallableDefinition implements DefinitionInterface
{
    private $method;

    public function __construct(callable $method)
    {
        $this->method = $method;
    }

    public function resolve(ContainerInterface $container)
    {
        $callback = $this->method;
        if ($container->has(Injector::class)) {
            return $container->get(Injector::class)->invoke($callback);
        }

        return $callback($container);
    }
}
