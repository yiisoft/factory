<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;

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
        return $callback($container);
    }
}
