<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use Yiisoft\Injector\Injector;

use function is_array;
use function is_object;

class CallableDefinition implements DefinitionInterface
{
    /**
     * @var callable
     */
    private $method;

    public function __construct(callable $method)
    {
        $this->method = $method;
    }

    public function resolve(ContainerInterface $container)
    {
        $callable = $this->prepareCallable($this->method, $container);

        /** @var Injector $injector */
        $injector = $container->get(Injector::class);

        return $injector->invoke($callable);
    }

    private function prepareCallable(callable $callable, ContainerInterface $container): callable
    {
        if (is_array($callable) && !is_object($callable[0])) {
            $reflection = new ReflectionMethod($callable[0], $callable[1]);
            if (!$reflection->isStatic()) {
                /** @var mixed */
                $callable[0] = $container->get($callable[0]);
            }
        }

        return $callable;
    }
}
