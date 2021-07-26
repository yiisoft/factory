<?php

declare(strict_types=1);

namespace Yiisoft\Factory\Tests;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Factory;
use Yiisoft\Factory\DependencyResolver;

final class TestHelper
{
    public static function createResolverContainer(
        ?ContainerInterface $container = null
    ): DependencyResolver {
        return new DependencyResolver(
            $container
        );
    }
}
