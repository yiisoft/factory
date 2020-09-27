<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use Yiisoft\Injector\Injector;

class CallableDefinition implements DefinitionInterface
{
    private static array $injectors = [];

    private $method;

    public function __construct(callable $method)
    {
        $this->method = $method;
    }

    public function resolve(ContainerInterface $container)
    {
        $callback = $this->method;
        if (class_exists(Injector::class)) {
            return $this->getInjector($container)->invoke($callback);
        }

        return $callback($container);
    }

    private function getInjector(ContainerInterface $container): Injector
    {
        $id = spl_object_hash($container);
        if (!array_key_exists($id, self::$injectors)) {
            self::$injectors[$id] = new Injector($container);
        }

        return self::$injectors[$id];
    }
}
