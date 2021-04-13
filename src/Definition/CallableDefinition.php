<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definition;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use Yiisoft\Injector\Injector;

use function is_array;
use function is_object;

class CallableDefinition implements DefinitionInterface
{
    /**
     * @var array|callable
     * @psalm-var callable|array{0:class-string,1:string}
     */
    private $method;

    /**
     * @param array|callable $method
     *
     * @psalm-param callable|array{0:class-string,1:string} $method
     */
    public function __construct($method)
    {
        $this->method = $method;
    }

    public function resolve(ContainerInterface $container)
    {
        $callable = $this->prepareCallable($this->method, $container);

        /** @psalm-suppress MixedMethodCall */
        return $container->get(Injector::class)->invoke($callable);
    }

    /**
     * @param array|callable $callable
     *
     * @psalm-param callable|array{0:class-string,1:string} $callable
     */
    private function prepareCallable($callable, ContainerInterface $container): callable
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
