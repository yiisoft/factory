<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests\Support;

use Psr\Container\ContainerInterface;

final class InvokableCarFactory
{
    public function __invoke(ContainerInterface $container): Car
    {
        /** @var EngineInterface $engine */
        $engine = $container->get('engine');
        return new Car($engine);
    }
}
