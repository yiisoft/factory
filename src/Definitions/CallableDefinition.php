<?php
namespace Yiisoft\Factory\Definitions;

use Psr\Container\ContainerInterface;
use yii\di\Container;

class CallableDefinition implements Definition
{
    private $method;

    public function __construct(callable $method)
    {
        $this->method = $method;
    }

    public function resolve(ContainerInterface $container, array $params = [])
    {
        $callback = $this->method;
        return $callback($container, $params);
    }
}
