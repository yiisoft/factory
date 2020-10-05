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
        return $container->get(Injector::class)->invoke($this->method);
    }
}
