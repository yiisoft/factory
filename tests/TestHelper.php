<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\FactoryContainer;

final class TestHelper
{
    public static function createFactoryContainer(
        ?ContainerInterface $container = null,
        ?Factory $factory = null
    ): FactoryContainer {
        return new FactoryContainer(
            $factory ?? new Factory(),
            $container
        );
    }
}
