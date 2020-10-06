<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use Yiisoft\Injector\Injector;

class CallableDefinition implements DefinitionInterface
{
    private $method;

    public function __construct($method)
    {
        $this->method = $method;
    }

    public function resolve(ContainerInterface $container)
    {
        $callable = $this->prepareCallable($this->method, $container);

        return $container->get(Injector::class)->invoke($callable);
    }

    private function prepareCallable($callable, ContainerInterface $container): callable
    {
        if (is_array($callable) && !is_object($callable[0])) {
            $reflection = new ReflectionMethod($callable[0], $callable[1]);
            if (!$reflection->isStatic()) {
                $callable[0] = $container->get($callable[0]);
            }
        }

        return $callable;
    }
}
