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
        if (class_exists(Injector::class)) {
            return (new Injector($container))->invoke($callback);
        }

        return $callback($container);
    }
}
